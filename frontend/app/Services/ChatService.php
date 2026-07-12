<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\ConversationSetting;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChatService
{
    public function ensureMessagingAccess(User $user): void
    {
        $isApprovedApplicant = $user->account_type === 'pwd_applicant'
            && $user->applicant_review_status === 'approved';
        $isEmployer = $user->account_type === 'employer';

        abort_unless($user->account_type === 'admin' || $isApprovedApplicant || $isEmployer, 403);
    }

    public function conversationsFor(User $user): Collection
    {
        $this->ensureMessagingAccess($user);

        return Conversation::query()
            ->forParticipant($user->id)
            ->visibleTo($user->id)
            ->with([
                'firstParticipant:id,name,first_name,last_name,company_name,account_type,applicant_review_status,employer_document_status,last_seen_at',
                'secondParticipant:id,name,first_name,last_name,company_name,account_type,applicant_review_status,employer_document_status,last_seen_at',
                'latestMessage.sender:id,name,first_name,last_name,company_name,account_type',
                'latestJobApplication.job:id,user_id,title',
                'settings' => fn ($query) => $query->where('user_id', $user->id),
            ])
            ->withCount([
                'messages as unread_count' => fn ($query) => $query
                    ->where('sender_id', '!=', $user->id)
                    ->whereNull('read_at'),
            ])
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->get();
    }

    public function contactsFor(User $user): Collection
    {
        $this->ensureMessagingAccess($user);

        $columns = ['id', 'name', 'first_name', 'last_name', 'company_name', 'account_type'];

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
                ->get($columns);
        }

        $supportContacts = User::query()
            ->where('account_type', 'admin')
            ->orderBy('name')
            ->get($columns);

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
                ->get($columns);

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
                ->get($columns);

            return $supportContacts->concat($applicantContacts);
        }

        return $supportContacts;
    }

    public function findConversationFor(User $user, Conversation $conversation, bool $withMessages = false): Conversation
    {
        $this->ensureMessagingAccess($user);
        abort_unless($conversation->hasParticipant($user), 403);

        $relations = [
            'firstParticipant:id,name,first_name,last_name,company_name,account_type,applicant_review_status,employer_document_status,last_seen_at',
            'secondParticipant:id,name,first_name,last_name,company_name,account_type,applicant_review_status,employer_document_status,last_seen_at',
            'latestJobApplication.job:id,user_id,title',
            'settings' => fn ($query) => $query->where('user_id', $user->id),
        ];

        if ($withMessages) {
            $relations['messages'] = fn ($query) => $query
                ->with(['sender:id,name,first_name,last_name,company_name,account_type', 'reactions'])
                ->oldest();
        }

        return $conversation->load($relations);
    }

    /**
     * Open the single non-job conversation used for platform support.
     */
    public function openConversation(User $user, User $contact): Conversation
    {
        $this->ensureMessagingAccess($user);
        abort_unless($this->canStartDirectConversation($user, $contact), 403);

        [$firstUserId, $secondUserId] = $this->participantIds($user, $contact);

        // An archived direct conversation can be restored. A deleted one is
        // deliberately excluded so starting again opens an empty new thread.
        $conversation = Conversation::query()
            ->forParticipant($firstUserId)
            ->forParticipant($secondUserId)
            ->where(fn ($query) => $query
                ->where('context_key', 'direct')
                ->orWhere('context_key', 'like', 'direct:%'))
            ->whereDoesntHave('settings', fn ($settings) => $settings
                ->where('user_id', $user->id)
                ->whereNotNull('deleted_at'))
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->first();

        if (! $conversation instanceof Conversation) {
            $hasPreviousDirectConversation = Conversation::query()
                ->forParticipant($firstUserId)
                ->forParticipant($secondUserId)
                ->where(fn ($query) => $query
                    ->where('context_key', 'direct')
                    ->orWhere('context_key', 'like', 'direct:%'))
                ->exists();

            $conversation = Conversation::create([
                'first_user_id' => $firstUserId,
                'second_user_id' => $secondUserId,
                // Keep the original key for existing installations, then use a
                // unique generation for every fresh conversation after delete.
                'context_key' => $hasPreviousDirectConversation
                    ? 'direct:'.Str::uuid()
                    : 'direct',
            ]);
        }

        $this->unarchiveFor($user, $conversation);

        return $conversation;
    }

    /**
     * Create or load the exact thread belonging to one job application.
     */
    public function openApplicationConversation(JobApplication $application): Conversation
    {
        $application->loadMissing(['applicant', 'job.employer']);
        $applicant = $application->applicant;
        $employer = $application->job?->employer;

        abort_unless($applicant instanceof User && $employer instanceof User, 404);
        abort_unless($this->canMessageForApplication($applicant, $employer, $application), 403);

        [$firstUserId, $secondUserId] = $this->participantIds($applicant, $employer);

        $conversation = Conversation::firstOrCreate(
            ['job_application_id' => $application->id],
            [
                'first_user_id' => $firstUserId,
                'second_user_id' => $secondUserId,
                'context_key' => "job_application:{$application->id}",
                'job_id' => $application->job_id,
            ]
        );

        abort_unless(
            (int) $conversation->first_user_id === $firstUserId
            && (int) $conversation->second_user_id === $secondUserId
            && (int) $conversation->job_id === (int) $application->job_id,
            409,
            'The application conversation context is inconsistent.'
        );

        return $conversation;
    }

    public function canSendInConversation(User $user, Conversation $conversation): bool
    {
        if (! $conversation->hasParticipant($user)) {
            return false;
        }

        $conversation->loadMissing([
            'firstParticipant',
            'secondParticipant',
            'jobApplication.applicant',
            'jobApplication.job.employer',
        ]);

        $recipient = $conversation->otherParticipant($user);

        if (! $recipient instanceof User) {
            return false;
        }

        if ($conversation->job_application_id !== null) {
            $application = $conversation->jobApplication;

            return $application instanceof JobApplication
                && $this->canMessageForApplication($user, $recipient, $application);
        }

        return $this->isDirectConversation($conversation)
            && $this->canStartDirectConversation($user, $recipient);
    }

    public function send(User $sender, Conversation $conversation, string $body, ?UploadedFile $attachment = null): Message
    {
        $conversation = $this->findConversationFor($sender, $conversation);
        abort_unless($this->canSendInConversation($sender, $conversation), 403);

        $body = trim((string) preg_replace('/[^\S\r\n]+/u', ' ', $body));
        $hasAttachment = $attachment instanceof UploadedFile && $attachment->isValid();
        abort_if(! preg_match('/\S/u', $body) && ! $hasAttachment, 422, 'A message cannot be empty.');
        $attachmentData = null;

        if ($hasAttachment) {
            $attachmentName = Str::of($attachment->getClientOriginalName())
                ->replace(["\r", "\n", '"'], '')
                ->limit(180, '')
                ->toString();
            $attachmentData = [
                'attachment_path' => $attachment->store("chat-attachments/{$conversation->id}", 'local'),
                'attachment_mime' => $attachment->getMimeType() ?: 'application/octet-stream',
                'attachment_name' => $attachmentName !== '' ? $attachmentName : 'attachment',
                'attachment_size' => (int) ($attachment->getSize() ?: 0),
            ];
        }

        return DB::transaction(function () use ($sender, $conversation, $body, $attachmentData) {
            /** @var Conversation $lockedConversation */
            $lockedConversation = Conversation::query()
                ->lockForUpdate()
                ->findOrFail($conversation->id);

            $message = $lockedConversation->messages()->create([
                'sender_id' => $sender->id,
                'body' => $body,
            ] + ($attachmentData ?? []));

            $lockedConversation->forceFill(['last_message_at' => $message->created_at])->save();
            ConversationSetting::query()
                ->where('conversation_id', $lockedConversation->id)
                ->whereNotNull('archived_at')
                ->update(['archived_at' => null]);

            return $message->load(['sender:id,name,first_name,last_name,company_name,account_type', 'reactions']);
        });
    }

    /**
     * @return array{conversation_id:int,message_ids:array<int,int>,read_at:Carbon,read_count:int}
     */
    public function markAsRead(User $reader, Conversation $conversation): array
    {
        $conversation = $this->findConversationFor($reader, $conversation);

        return DB::transaction(function () use ($reader, $conversation) {
            $readAt = now();
            $messageIds = $conversation->messages()
                ->where('sender_id', '!=', $reader->id)
                ->whereNull('read_at')
                ->lockForUpdate()
                ->pluck('id');

            if ($messageIds->isNotEmpty()) {
                Message::query()
                    ->whereIn('id', $messageIds)
                    ->update(['read_at' => $readAt]);
            }

            return [
                'conversation_id' => (int) $conversation->id,
                'message_ids' => $messageIds->map(fn ($id) => (int) $id)->all(),
                'read_at' => $readAt,
                'read_count' => $messageIds->count(),
            ];
        });
    }

    public function unreadCount(User $user): int
    {
        return Message::query()
            ->whereNull('read_at')
            ->where('sender_id', '!=', $user->id)
            ->whereHas('conversation', fn ($query) => $query->forParticipant($user->id))
            ->count();
    }

    public function totalUnreadCount(User $user): int
    {
        return $this->unreadCount($user);
    }

    public function inboxMessageCursor(User $user): int
    {
        return max(0, (int) Message::query()
            ->whereHas('conversation', fn ($query) => $query
                ->forParticipant($user->id)
                ->visibleTo($user->id))
            ->max('id'));
    }

    public function messagePayload(Message $message): array
    {
        $message->loadMissing(['sender:id,name,first_name,last_name,company_name,account_type', 'reactions']);

        return [
            'id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'body' => $message->body,
            'sent_at' => $message->created_at?->toIso8601String(),
            'read_at' => $message->read_at?->toIso8601String(),
            'is_read' => $message->read_at !== null,
            'sender' => [
                'id' => $message->sender->id,
                'name' => $this->displayName($message->sender),
                'initials' => $this->initials($message->sender),
                'account_type' => $message->sender->account_type,
            ],
            'reactions' => $this->messageReactionsPayload($message),
            'attachment' => $message->attachment_path ? [
                'url' => route('messages.attachment', [
                    'conversation' => $message->conversation_id,
                    'message' => $message->id,
                ]),
                'mime' => $message->attachment_mime,
                'name' => $message->attachment_name,
                'size' => (int) $message->attachment_size,
            ] : null,
        ];
    }

    /** @return array<int, array{emoji:string,count:int,user_ids:array<int,int>}> */
    public function messageReactionsPayload(Message $message): array
    {
        $message->loadMissing('reactions');

        return $message->reactions
            ->groupBy('emoji')
            ->sortKeys()
            ->map(fn ($reactions, string $emoji) => [
                'emoji' => $emoji,
                'count' => $reactions->count(),
                'user_ids' => $reactions->pluck('user_id')->map(fn ($id) => (int) $id)->values()->all(),
            ])
            ->values()
            ->all();
    }

    public function displayName(User $user): string
    {
        if ($user->account_type === 'employer' && trim((string) $user->company_name) !== '') {
            return trim($user->company_name);
        }

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

    /** @return array{0:int,1:int} */
    private function participantIds(User $firstUser, User $secondUser): array
    {
        return collect([$firstUser->id, $secondUser->id])
            ->sort()
            ->values()
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function canMessagePlatformSupport(User $firstUser, User $secondUser): bool
    {
        if ($firstUser->is($secondUser)) {
            return false;
        }

        $roles = collect([$firstUser, $secondUser])->keyBy('account_type');
        $applicant = $roles->get('pwd_applicant');
        $employer = $roles->get('employer');

        if (! $roles->has('admin')) {
            return false;
        }

        if ($applicant instanceof User) {
            return $applicant->applicant_review_status === 'approved';
        }

        if ($employer instanceof User) {
            return true;
        }

        return false;
    }

    private function canStartDirectConversation(User $firstUser, User $secondUser): bool
    {
        return $this->canMessagePlatformSupport($firstUser, $secondUser)
            || $this->canMessageThroughSharedApplication($firstUser, $secondUser);
    }

    private function canMessageThroughSharedApplication(User $firstUser, User $secondUser): bool
    {
        if ($firstUser->is($secondUser)) {
            return false;
        }

        $roles = collect([$firstUser, $secondUser])->keyBy('account_type');
        $applicant = $roles->get('pwd_applicant');
        $employer = $roles->get('employer');

        if (! ($applicant instanceof User && $employer instanceof User)) {
            return false;
        }

        return $applicant->applicant_review_status === 'approved'
            && JobApplication::query()
                ->where('applicant_id', $applicant->id)
                ->whereHas('job', fn ($query) => $query->where('user_id', $employer->id))
                ->exists();
    }

    private function isDirectConversation(Conversation $conversation): bool
    {
        return $conversation->context_key === 'direct'
            || str_starts_with((string) $conversation->context_key, 'direct:');
    }

    private function unarchiveFor(User $user, Conversation $conversation): void
    {
        ConversationSetting::query()->updateOrCreate(
            ['conversation_id' => $conversation->id, 'user_id' => $user->id],
            ['archived_at' => null]
        );
    }

    private function canMessageForApplication(
        User $firstUser,
        User $secondUser,
        JobApplication $application
    ): bool {
        if ($firstUser->is($secondUser)) {
            return false;
        }

        $roles = collect([$firstUser, $secondUser])->keyBy('account_type');
        $applicant = $roles->get('pwd_applicant');
        $employer = $roles->get('employer');

        if (! ($applicant instanceof User && $employer instanceof User)) {
            return false;
        }

        $application->loadMissing('job');

        return (int) $application->applicant_id === (int) $applicant->id
            && (int) $application->job?->user_id === (int) $employer->id
            && $applicant->applicant_review_status === 'approved';
    }
}
