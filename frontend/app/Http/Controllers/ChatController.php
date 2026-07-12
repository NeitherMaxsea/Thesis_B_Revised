<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Events\MessagesRead;
use App\Http\Requests\StoreMessageRequest;
use App\Models\Conversation;
use App\Models\ConversationSetting;
use App\Models\Message;
use App\Models\MessageReaction;
use App\Models\User;
use App\Services\ChatService;
use App\Services\RealtimeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
            ->findOrFail($conversation);
        $afterMessageId = max(0, $request->integer('after'));

        $messages = $conversation->messages()
            ->where('id', '>', $afterMessageId)
            ->with('sender:id,name,first_name,last_name,company_name,account_type')
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
            ->with('sender:id,name,first_name,last_name,company_name,account_type')
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
