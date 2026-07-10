<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\User;
use App\Services\ChatService;
use App\Services\RealtimeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function __construct(
        private readonly ChatService $chatService,
        private readonly RealtimeService $realtime,
    ) {
    }

    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $this->chatService->ensureMessagingAccess($user);

        $conversations = $this->chatService->conversationsFor($user);
        $contacts = $this->chatService->contactsFor($user);
        $selectedConversation = null;

        if ($request->filled('conversation')) {
            $conversation = Conversation::findOrFail($request->integer('conversation'));
            $selectedConversation = $this->chatService->findConversationFor($user, $conversation, true);
        } elseif ($request->filled('recipient')) {
            $contact = $contacts->firstWhere('id', $request->integer('recipient'));
            abort_unless($contact instanceof User, 403);
            $selectedConversation = $this->chatService->openConversation($user, $contact);
            $selectedConversation = $this->chatService->findConversationFor($user, $selectedConversation, true);
            $conversations = $this->chatService->conversationsFor($user);
        } elseif ($conversations->isNotEmpty()) {
            $selectedConversation = $this->chatService->findConversationFor($user, $conversations->first(), true);
        }

        return view('dashboard.messages', [
            'user' => $user,
            'conversations' => $conversations,
            'contacts' => $contacts,
            'selectedConversation' => $selectedConversation,
            'selectedContact' => $selectedConversation?->otherParticipant($user),
            'canSend' => $selectedConversation
                ? $this->chatService->canSendInConversation($user, $selectedConversation)
                : false,
            'chatService' => $this->chatService,
        ]);
    }

    public function store(Request $request, Conversation $conversation): JsonResponse|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $message = $this->chatService->send($user, $conversation, $validated['body']);

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
}
