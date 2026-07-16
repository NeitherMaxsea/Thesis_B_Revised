<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Events\MessagesRead;
use App\Http\Requests\StoreMessageRequest;
use App\Models\Conversation;
use App\Models\ConversationSetting;
use App\Models\JobApplication;
use App\Models\Message;
use App\Models\MessageReaction;
use App\Models\User;
use App\Services\ChatService;
use App\Services\RealtimeService;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function __construct(
        private readonly ChatService $chatService,
        private readonly RealtimeService $realtime,
    ) {}

    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $this->chatService->ensureMessagingAccess($user);

        $conversations = $this->chatService->conversationsFor($user);
        $contacts = $this->chatService->contactsFor($user);
        $selectedBase = null;

        if ($request->filled('conversation')) {
            // Scope the lookup itself so changing the URL cannot enumerate a
            // conversation that belongs to another user.
            $selectedBase = Conversation::query()
                ->forParticipant($user->id)
                ->visibleTo($user->id)
                ->findOrFail($request->integer('conversation'));
        } elseif ($conversations->isNotEmpty()) {
            $selectedBase = $conversations->first();
        }

        $selectedConversation = null;

        if ($selectedBase instanceof Conversation) {
            $selectedConversation = $this->chatService->findConversationFor($user, $selectedBase, true);
            $synchronizedTimeline = $this->chatService
                ->synchronizeAcknowledgedRequirementsTimeline($selectedConversation);

            if ($synchronizedTimeline !== null) {
                $conversations = $this->chatService->conversationsFor($user);
                $selectedConversation = $this->chatService->findConversationFor(
                    $user,
                    $selectedConversation->fresh(),
                    true,
                );
            }
        }

        return view('dashboard.messages', [
            'user' => $user,
            'conversations' => $conversations,
            'contacts' => $contacts,
            'selectedConversation' => $selectedConversation,
            'selectedContact' => $selectedConversation?->otherParticipant($user),
            'chatService' => $this->chatService,
            'unreadMessageCount' => $this->chatService->totalUnreadCount($user),
            'inboxMessageCursor' => $this->chatService->inboxMessageCursor($user),
        ]);
    }

    public function start(Request $request): JsonResponse|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $validated = $request->validate([
            'recipient_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
                Rule::notIn([$user->id]),
            ],
        ]);

        $contact = User::findOrFail($validated['recipient_id']);
        $conversation = $this->chatService->openConversation($user, $contact);
        $url = route('messages.index', ['conversation' => $conversation->id]);

        if ($request->expectsJson()) {
            return response()->json([
                'conversation_id' => $conversation->id,
                'url' => $url,
            ], 201);
        }

        return redirect($url);
    }

    public function store(StoreMessageRequest $request, string $conversation): JsonResponse|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $conversation = Conversation::query()
            ->forParticipant($user->id)
            ->findOrFail($conversation);
        $message = $this->chatService->send(
            $user,
            $conversation,
            $request->validated('body') ?? '',
            $request->file('attachment'),
        );

        $this->realtime->broadcast(new MessageSent($message));

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->chatService->messagePayload($message),
            ], 201);
        }

        return redirect()
            ->route('messages.index', ['conversation' => $conversation->id])
            ->with('status', 'Message sent.');
    }

    /**
     * Only the employer who owns the job may move an application through its
     * hiring stages. The applicant sees the same state in their conversation.
     */
    public function updateApplicationStage(Request $request, string $conversation): JsonResponse|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $conversation = Conversation::query()
            ->forParticipant($user->id)
            ->with('jobApplication.job:id,user_id')
            ->findOrFail($conversation);
        $application = $conversation->jobApplication;

        abort_unless(
            $user->account_type === 'employer'
                && $application instanceof JobApplication
                && (int) $application->job?->user_id === (int) $user->id,
            403
        );

        $status = $request->validate([
            'status' => ['required', 'string', Rule::in(array_keys(JobApplication::hiringStages()))],
        ])['status'];

        $application->update(['status' => $status]);
        $timeline = $application->fresh()->hiringStagePayload();

        if ($request->expectsJson()) {
            return response()->json(['application_timeline' => $timeline]);
        }

        return redirect()
            ->route('messages.index', ['conversation' => $conversation->id])
            ->with('status', 'Hiring stage updated to '.$timeline['title'].'.');
    }

    /**
     * Employers use the hiring-actions menu to send a structured interview,
     * skills-assessment, or documents card to an applicant.
     */
    public function sendHiringAction(Request $request, string $conversation): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $conversation = Conversation::query()
            ->forParticipant($user->id)
            ->with('jobApplication.job:id,user_id')
            ->findOrFail($conversation);
        $application = $conversation->jobApplication;

        abort_unless(
            $user->account_type === 'employer'
                && $application instanceof JobApplication
                && (int) $application->job?->user_id === (int) $user->id,
            403
        );

        $action = $request->validate([
            'action' => ['required', 'string', Rule::in(['interview', 'assessment', 'documents'])],
        ])['action'];
        $targetStage = null;

        if ($action === 'interview') {
            $data = $request->validate([
                'interview_date' => ['required', 'date'],
                'interview_time' => ['required', 'date_format:H:i'],
                'interview_method' => ['required', 'string', 'max:80'],
                'meeting_link' => ['nullable', 'url', 'max:1000'],
                'details' => ['nullable', 'string', 'max:1500'],
            ]);
            $date = Carbon::parse($data['interview_date'])->format('M j, Y');
            $metadata = [
                'action' => 'interview',
                'eyebrow' => 'Interview invitation',
                'title' => 'Interview schedule',
                'intro' => 'You have been shortlisted for an interview. Please review the details below.',
                'details' => array_values(array_filter([
                    ['label' => 'Date', 'value' => $date],
                    ['label' => 'Time', 'value' => Carbon::createFromFormat('H:i', $data['interview_time'])->format('g:i A')],
                    ['label' => 'Format', 'value' => $data['interview_method']],
                    ['label' => 'Meeting link', 'value' => $data['meeting_link'] ?? null],
                    ['label' => 'Additional details', 'value' => $data['details'] ?? null],
                ], fn (array $item) => filled($item['value']))),
            ];
            $body = "You have an interview invitation for {$date}.";
            $targetStage = 'interview_schedule';
        } elseif ($action === 'assessment') {
            $data = $request->validate([
                'assessment_title' => ['required', 'string', 'max:160'],
                'assessment_link' => ['nullable', 'url', 'max:1000'],
                'assessment_instructions' => ['required', 'string', 'max:1500'],
            ]);
            $metadata = [
                'action' => 'assessment',
                'eyebrow' => 'Skills assessment',
                'title' => $data['assessment_title'],
                'intro' => 'May ipinadalang skills assessment ang employer para mas maipakita mo ang iyong kakayahan.',
                'details' => array_values(array_filter([
                    ['label' => 'Panuto', 'value' => $data['assessment_instructions']],
                    ['label' => 'Assessment link', 'value' => $data['assessment_link'] ?? null],
                ], fn (array $item) => filled($item['value']))),
            ];
            $body = "May skills assessment para sa iyo: {$data['assessment_title']}.";
        } else {
            $data = $request->validate([
                'documents' => ['required', 'array', 'min:1', 'max:12'],
                'documents.*' => ['required', 'string', 'max:160'],
            ]);
            $items = collect($data['documents'])
                ->map(fn (string $item) => trim($item))
                ->filter()
                ->unique(fn (string $item) => mb_strtolower($item))
                ->take(12)
                ->values()
                ->all();
            abort_if($items === [], 422, 'Maglagay ng kahit isang dokumento.');
            $metadata = [
                'action' => 'documents',
                'eyebrow' => 'Pre-employment documents',
                'title' => 'Pre-employment documents',
                'intro' => 'Please prepare and upload the selected documents.',
                'items' => $items,
            ];
            $body = 'May hinihinging pre-employment documents ang employer.';
            $targetStage = 'pre_employment_requirements';
        }

        [$message, $timeline] = DB::transaction(function () use ($user, $conversation, $application, $body, $metadata, $targetStage) {
            $lockedApplication = JobApplication::query()->lockForUpdate()->findOrFail($application->id);
            $timeline = $lockedApplication->hiringStagePayload();

            if ($targetStage !== null
                && JobApplication::hiringStages()[$targetStage]['number'] > $timeline['number']) {
                $lockedApplication->update(['status' => $targetStage]);
                $timeline = $lockedApplication->fresh()->hiringStagePayload();
            }

            $message = $this->chatService->sendHiringActionCard($user, $conversation, $body, $metadata);

            return [$message, $timeline];
        });

        $this->realtime->broadcast(new MessageSent($message));

        return response()->json([
            'message' => $this->chatService->messagePayload($message),
            'application_timeline' => $timeline,
        ], 201);
    }

    /**
     * Lets only the applicant confirm that they have received an employer's
     * requirement card. The confirmation also becomes a normal chat message
     * so the employer receives it even without refreshing the conversation.
     */
    public function acknowledgeRequirements(Request $request, string $conversation, string $message): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $conversation = Conversation::query()
            ->forParticipant($user->id)
            ->with('jobApplication')
            ->findOrFail($conversation);
        $application = $conversation->jobApplication;

        abort_unless(
            $user->account_type === 'pwd_applicant'
                && $application instanceof JobApplication
                && (int) $application->applicant_id === (int) $user->id,
            403
        );

        [$requirementsCard, $reply, $timeline] = DB::transaction(function () use ($conversation, $message, $user, $application) {
            $requirementsCard = Message::query()
                ->where('conversation_id', $conversation->id)
                ->lockForUpdate()
                ->findOrFail($message);

            abort_unless(
                $requirementsCard->message_type === Message::TYPE_REQUIREMENTS_CARD
                    && (int) $requirementsCard->sender_id !== (int) $user->id,
                422,
                'This requirements card cannot be confirmed.'
            );

            $lockedApplication = JobApplication::query()
                ->lockForUpdate()
                ->findOrFail($application->id);
            $metadata = $requirementsCard->metadata ?? [];
            $reply = null;
            $timeline = $lockedApplication->hiringStagePayload();

            if (empty($metadata['acknowledged_at'])) {
                $metadata['acknowledged_at'] = now()->toIso8601String();
                $metadata['acknowledged_by'] = $user->id;
                $nextStage = $metadata['next_stage'] ?? $lockedApplication->nextHiringStage();

                if (is_string($nextStage)
                    && isset(JobApplication::hiringStages()[$nextStage])
                    && JobApplication::hiringStages()[$nextStage]['number'] > $timeline['number']) {
                    $lockedApplication->update(['status' => $nextStage]);
                    $timeline = $lockedApplication->fresh()->hiringStagePayload();
                }

                $metadata['timeline_processed_at'] = now()->toIso8601String();
                $metadata['advanced_to'] = $timeline['status'];
                $requirementsCard->update(['metadata' => $metadata]);
                $requirementsCard->refresh();

                $reply = $this->chatService->send(
                    $user,
                    $conversation,
                    'Nabasa ko po ang mga requirement. Ihahanda ko po ang mga ito.',
                );
            }

            return [$requirementsCard, $reply, $timeline];
        });

        if ($reply instanceof Message) {
            $this->realtime->broadcast(new MessageSent($reply));
        }

        return response()->json([
            'requirements_card' => $this->chatService->messagePayload($requirementsCard),
            'reply' => $reply instanceof Message ? $this->chatService->messagePayload($reply) : null,
            'application_timeline' => $timeline,
        ]);
    }

    /**
     * Lets the applicant make one clear decision about an employer's
     * interview invitation: attend as scheduled or request a new schedule.
     */
    public function respondToInterview(Request $request, string $conversation, string $message): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $conversation = Conversation::query()
            ->forParticipant($user->id)
            ->with('jobApplication')
            ->findOrFail($conversation);
        $application = $conversation->jobApplication;

        abort_unless(
            $user->account_type === 'pwd_applicant'
                && $application instanceof JobApplication
                && (int) $application->applicant_id === (int) $user->id,
            403
        );

        $response = $request->validate([
            'response' => ['required', 'string', Rule::in(['confirmed', 'change_requested'])],
        ])['response'];

        [$interviewCard, $reply] = DB::transaction(function () use ($conversation, $message, $user, $response) {
            $interviewCard = Message::query()
                ->where('conversation_id', $conversation->id)
                ->lockForUpdate()
                ->findOrFail($message);
            $metadata = $interviewCard->metadata ?? [];

            abort_unless(
                $interviewCard->message_type === Message::TYPE_HIRING_ACTION
                    && ($metadata['action'] ?? null) === 'interview'
                    && (int) $interviewCard->sender_id !== (int) $user->id,
                422,
                'This interview invitation cannot be answered.'
            );
            abort_if(
                filled($metadata['attendance_response'] ?? null),
                422,
                'You have already responded to this interview invitation.'
            );

            $metadata['attendance_response'] = $response;
            $metadata['attendance_responded_at'] = now()->toIso8601String();
            $metadata['attendance_responded_by'] = $user->id;
            $interviewCard->update(['metadata' => $metadata]);
            $interviewCard->refresh();

            $reply = $this->chatService->send(
                $user,
                $conversation,
                $response === 'confirmed'
                    ? 'I confirm that I will attend the interview as scheduled.'
                    : 'I would like to request a change to the interview schedule.',
            );

            return [$interviewCard, $reply];
        });

        $this->realtime->broadcast(new MessageSent($reply));

        return response()->json([
            'interview_card' => $this->chatService->messagePayload($interviewCard),
            'reply' => $this->chatService->messagePayload($reply),
        ]);
    }

    public function markRead(Request $request, string $conversation): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $conversation = Conversation::query()
            ->forParticipant($user->id)
            ->findOrFail($conversation);
        $receipt = $this->chatService->markAsRead($user, $conversation);
        $this->broadcastReadReceipt($user, $receipt);

        return response()->json([
            'read' => [
                'conversation_id' => $receipt['conversation_id'],
                'reader_id' => (int) $user->id,
                'message_ids' => $receipt['message_ids'],
                'read_at' => $receipt['read_at']->toIso8601String(),
                'read_count' => $receipt['read_count'],
            ],
            'unread_message_count' => $this->chatService->totalUnreadCount($user),
        ]);
    }

    public function presence(Request $request, string $conversation): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $conversation = Conversation::query()
            ->forParticipant($user->id)
            ->with(['firstParticipant:id,last_seen_at', 'secondParticipant:id,last_seen_at'])
            ->findOrFail($conversation);
        $contact = $conversation->otherParticipant($user);

        return response()->json([
            'last_seen_at' => $contact?->last_seen_at?->toIso8601String(),
        ]);
    }

    public function attachment(Request $request, string $conversation, string $message)
    {
        /** @var User $user */
        $user = $request->user();
        $conversation = Conversation::query()
            ->forParticipant($user->id)
            ->findOrFail($conversation);
        $message = $conversation->messages()
            ->whereNotNull('attachment_path')
            ->findOrFail($message);
        $disk = Storage::disk('local');

        abort_unless($disk->exists($message->attachment_path), 404);

        return response()->file($disk->path($message->attachment_path), [
            'Content-Type' => $message->attachment_mime,
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    /**
     * A short-interval recovery path when a WebSocket connection is restarting
     * or unavailable. The browser de-duplicates these persisted messages when
     * Reverb is delivering normally, so it is safe to use alongside Echo.
     */
    public function updates(Request $request, string $conversation): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $conversation = Conversation::query()
            ->forParticipant($user->id)
            ->with('jobApplication')
            ->findOrFail($conversation);
        $afterMessageId = max(0, $request->integer('after'));

        $messages = $conversation->messages()
            ->where('id', '>', $afterMessageId)
            ->with('sender:id,name,first_name,last_name,company_name,account_type,profile_photo_path')
            ->oldest()
            ->limit(100)
            ->get();
        $readReceipts = $conversation->messages()
            ->where('sender_id', $user->id)
            ->whereNotNull('read_at')
            ->latest('id')
            ->limit(100)
            ->get(['id', 'read_at']);
        $reactionMessages = $conversation->messages()
            ->with('reactions')
            ->latest('id')
            ->limit(100)
            ->get();
        $otherParticipant = $conversation->otherParticipant($user);

        return response()->json([
            'messages' => $messages
                ->map(fn (Message $message) => $this->chatService->messagePayload($message))
                ->values(),
            'read_receipts' => $readReceipts
                ->map(fn (Message $message) => [
                    'id' => $message->id,
                    'read_at' => $message->read_at?->toIso8601String(),
                ])
                ->values(),
            'typing' => [
                'user_id' => $otherParticipant?->id,
                'is_typing' => $otherParticipant !== null && Cache::has(
                    $this->typingCacheKey($conversation->id, $otherParticipant->id)
                ),
            ],
            'message_reactions' => $reactionMessages
                ->map(fn (Message $message) => [
                    'message_id' => $message->id,
                    'reactions' => $this->chatService->messageReactionsPayload($message),
                ])
                ->values(),
            'application_timeline' => $conversation->jobApplication?->hiringStagePayload(),
        ]);
    }

    /**
     * Inbox-level recovery for the conversation list. This means a recipient
     * still sees a new or restarted conversation without reloading when the
     * WebSocket server is temporarily unavailable.
     */
    public function inboxUpdates(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $afterMessageId = max(0, $request->integer('after'));

        $messages = Message::query()
            ->where('id', '>', $afterMessageId)
            ->where('sender_id', '!=', $user->id)
            ->whereHas('conversation', fn ($query) => $query
                ->forParticipant($user->id)
                ->visibleTo($user->id))
            ->with('sender:id,name,first_name,last_name,company_name,account_type,profile_photo_path')
            ->latest('id')
            ->limit(100)
            ->get()
            ->sortBy('id')
            ->values();

        return response()->json([
            'messages' => $messages
                ->map(fn (Message $message) => $this->chatService->messagePayload($message))
                ->values(),
            'next_after' => (int) ($messages->max('id') ?? $afterMessageId),
        ]);
    }

    public function archive(Request $request, string $conversation): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $conversation = Conversation::query()
            ->forParticipant($user->id)
            ->findOrFail($conversation);

        ConversationSetting::query()->updateOrCreate(
            ['conversation_id' => $conversation->id, 'user_id' => $user->id],
            ['archived_at' => now()]
        );

        return response()->json(['archived' => true]);
    }

    public function deleteForUser(Request $request, string $conversation): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $conversation = Conversation::query()
            ->forParticipant($user->id)
            ->findOrFail($conversation);

        ConversationSetting::query()->updateOrCreate(
            ['conversation_id' => $conversation->id, 'user_id' => $user->id],
            ['archived_at' => now(), 'deleted_at' => now()]
        );

        return response()->json(['deleted' => true]);
    }

    public function mute(Request $request, string $conversation): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $conversation = Conversation::query()
            ->forParticipant($user->id)
            ->findOrFail($conversation);
        $duration = $request->validate([
            'duration' => ['required', Rule::in(['15m', '1h', '8h', 'forever', 'off'])],
        ])['duration'];
        $mutedUntil = match ($duration) {
            '15m' => now()->addMinutes(15),
            '1h' => now()->addHour(),
            '8h' => now()->addHours(8),
            'forever' => now()->addYears(100),
            default => null,
        };

        $setting = ConversationSetting::query()->updateOrCreate(
            ['conversation_id' => $conversation->id, 'user_id' => $user->id],
            ['muted_until' => $mutedUntil]
        );

        return response()->json([
            'muted_until' => $setting->muted_until?->toIso8601String(),
        ]);
    }

    public function react(Request $request, string $conversation, string $message): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $conversation = Conversation::query()
            ->forParticipant($user->id)
            ->findOrFail($conversation);
        $message = $conversation->messages()->findOrFail($message);
        $emoji = $request->validate([
            'emoji' => ['required', 'string', Rule::in(['👍', '❤️', '😊'])],
        ])['emoji'];
        $reaction = MessageReaction::query()
            ->where('message_id', $message->id)
            ->where('user_id', $user->id)
            ->first();

        if ($reaction && $reaction->emoji === $emoji) {
            $reaction->delete();
        } elseif ($reaction) {
            $reaction->update(['emoji' => $emoji]);
        } else {
            MessageReaction::create([
                'message_id' => $message->id,
                'user_id' => $user->id,
                'emoji' => $emoji,
            ]);
        }

        return response()->json([
            'message_id' => $message->id,
            'reactions' => $this->chatService->messageReactionsPayload($message->fresh()->load('reactions')),
        ]);
    }

    public function typing(Request $request, string $conversation): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $conversation = Conversation::query()
            ->forParticipant($user->id)
            ->findOrFail($conversation);

        Cache::put($this->typingCacheKey($conversation->id, $user->id), true, now()->addSeconds(5));

        return response()->json([], 204);
    }

    private function typingCacheKey(int $conversationId, int $userId): string
    {
        return "chat.typing.{$conversationId}.{$userId}";
    }

    /** @param array{conversation_id:int,message_ids:array<int,int>,read_at:mixed,read_count:int} $receipt */
    private function broadcastReadReceipt(User $reader, array $receipt): void
    {
        if ($receipt['read_count'] === 0) {
            return;
        }

        $this->realtime->broadcast(new MessagesRead(
            $receipt['conversation_id'],
            (int) $reader->id,
            $receipt['message_ids'],
            $receipt['read_at']->toIso8601String(),
        ));
    }
}
