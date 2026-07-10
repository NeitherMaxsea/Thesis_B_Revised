<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChatService
{
    public function __construct(private readonly EmployerDocumentService $documents)
    {
    }

    /**
     * Messaging is limited to approved applicant/support pairs and to the
     * applicant-employer pair created by an actual job application.
     */
    public function ensureMessagingAccess(User $user): void
    {
        $isApprovedApplicant = $user->account_type === 'pwd_applicant'
            && $user->applicant_review_status === 'approved';
        $isVerifiedEmployer = $user->account_type === 'employer'
            && $user->employer_document_status === 'valid';

        abort_unless($user->account_type === 'admin' || $isApprovedApplicant || $isVerifiedEmployer, 403);
    }

    public function conversationsFor(User $user): Collection
    {
        $this->ensureMessagingAccess($user);

        return Conversation::query()
            ->forParticipant($user->id)
            ->with([
                'firstParticipant:id,name,first_name,last_name,account_type',
                'secondParticipant:id,name,first_name,last_name,account_type',
                'latestMessage.sender:id,name,first_name,last_name',
            ])
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->get();
    }

    public function contactsFor(User $user): Collection
    {
        $this->ensureMessagingAccess($user);

        if ($user->account_type === 'admin') {
            return User::query()
                ->where(function ($query) {
                    $query->where(function ($query) {
                        $query->where('account_type', 'pwd_applicant')
                            ->where('applicant_review_status', 'approved');
                    })->orWhere(function ($query) {
                        $query->where('account_type', 'employer')
                            ->where('employer_document_status', 'valid');
                    });
                })
                ->orderBy('first_name')
                ->orderBy('name')
                ->get(['id', 'name', 'first_name', 'last_name', 'account_type']);
        }

        $supportContacts = User::query()
            ->where('account_type', 'admin')
            ->orderBy('name')
            ->get(['id', 'name', 'first_name', 'last_name', 'account_type']);

        if ($user->account_type === 'pwd_applicant') {
            $employerIds = Job::query()
                ->whereHas('applications', fn ($query) => $query->where('applicant_id', $user->id))
                ->pluck('user_id')
                ->unique();

            $employerContacts = User::query()
                ->whereIn('id', $employerIds)
                ->where('account_type', 'employer')
                ->where('employer_document_status', 'valid')
                ->orderBy('company_name')
                ->get(['id', 'name', 'first_name', 'last_name', 'account_type']);

            return $supportContacts->concat($employerContacts);
        }

        if ($user->account_type === 'employer') {
            $applicantIds = JobApplication::query()
                ->whereHas('job', fn ($query) => $query->where('user_id', $user->id))
                ->pluck('applicant_id')
                ->unique();

            $applicantContacts = User::query()
                ->whereIn('id', $applicantIds)
                ->where('account_type', 'pwd_applicant')
                ->where('applicant_review_status', 'approved')
                ->orderBy('first_name')
                ->get(['id', 'name', 'first_name', 'last_name', 'account_type']);

            return $supportContacts->concat($applicantContacts);
        }

        return $supportContacts;
    }

    public function findConversationFor(User $user, Conversation $conversation, bool $withMessages = false): Conversation
    {
        $this->ensureMessagingAccess($user);
        abort_unless($conversation->hasParticipant($user), 403);

        $relations = [
            'firstParticipant:id,name,first_name,last_name,account_type',
            'secondParticipant:id,name,first_name,last_name,account_type',
        ];

        if ($withMessages) {
            $relations['messages'] = fn ($query) => $query->with('sender:id,name,first_name,last_name')
                ->oldest();
        }

        return $conversation->load($relations);
    }

    public function openConversation(User $user, User $contact): Conversation
    {
        $this->ensureMessagingAccess($user);
        abort_unless($this->canMessage($user, $contact), 403);

        [$firstUserId, $secondUserId] = collect([$user->id, $contact->id])
            ->sort()
            ->values()
            ->all();

        return Conversation::firstOrCreate([
            'first_user_id' => $firstUserId,
            'second_user_id' => $secondUserId,
        ]);
    }

    public function canSendInConversation(User $user, Conversation $conversation): bool
    {
        if (! $conversation->hasParticipant($user)) {
            return false;
        }

        $conversation->load([
            'firstParticipant:id,name,first_name,last_name,account_type,applicant_review_status,employer_document_status',
            'secondParticipant:id,name,first_name,last_name,account_type,applicant_review_status,employer_document_status',
        ]);
        $recipient = $conversation->otherParticipant($user);

        return $recipient instanceof User && $this->canMessage($user, $recipient);
    }

    public function send(User $sender, Conversation $conversation, string $body): Message
    {
        $conversation = $this->findConversationFor($sender, $conversation);
        abort_unless($this->canSendInConversation($sender, $conversation), 403);

        // Preserve line breaks so employers can send readable, step-by-step
        // requirements while still normalizing accidental repeated spaces.
        $body = trim((string) preg_replace('/[^\S\r\n]+/', ' ', $body));

        abort_if(! preg_match('/\S/', $body), 422, 'A message cannot be empty.');

        return DB::transaction(function () use ($sender, $conversation, $body) {
            $message = $conversation->messages()->create([
                'sender_id' => $sender->id,
                'body' => $body,
            ]);

            $conversation->forceFill(['last_message_at' => $message->created_at])->save();

            return $message->load('sender:id,name,first_name,last_name');
        });
    }

    public function messagePayload(Message $message): array
    {
        $message->loadMissing('sender:id,name,first_name,last_name');

        return [
            'id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'body' => $message->body,
            'sent_at' => $message->created_at?->toIso8601String(),
            'sender' => [
                'id' => $message->sender->id,
                'name' => $this->displayName($message->sender),
                'initials' => $this->initials($message->sender),
            ],
        ];
    }

    public function displayName(User $user): string
    {
        $profileName = trim(implode(' ', array_filter([$user->first_name, $user->last_name])));

        return $profileName !== '' ? $profileName : $user->name;
    }

    public function initials(User $user): string
    {
        return Str::of($this->displayName($user))
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $name) => Str::upper(Str::substr($name, 0, 1)))
            ->implode('');
    }

    private function canMessage(User $firstUser, User $secondUser): bool
    {
        if ($firstUser->is($secondUser)) {
            return false;
        }

        $roles = collect([$firstUser, $secondUser])->keyBy('account_type');
        $applicant = $roles->get('pwd_applicant');
        $employer = $roles->get('employer');

        $canMessagePlatformSupport = $roles->has('admin') && (
            ($applicant instanceof User && $applicant->applicant_review_status === 'approved')
            || ($employer instanceof User && $employer->employer_document_status === 'valid')
        );

        if ($canMessagePlatformSupport) {
            return true;
        }

        if (! ($applicant instanceof User && $employer instanceof User)) {
            return false;
        }

        if ($this->documents->refreshStatus($employer) !== 'valid') {
            return false;
        }

        return JobApplication::query()
            ->where('applicant_id', $applicant->id)
            ->whereHas('job', fn ($query) => $query->where('user_id', $employer->id))
            ->exists();
    }
}
