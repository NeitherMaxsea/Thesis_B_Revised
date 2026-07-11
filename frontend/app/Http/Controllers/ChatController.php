<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Events\MessagesRead;
use App\Http\Requests\StoreMessageRequest;
use App\Models\Conversation;
use App\Models\User;
use App\Services\ChatService;
use App\Services\RealtimeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $message = $this->chatService->send($user, $conversation, $request->validated('body'));

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
