<?php

namespace App\Events;

use App\Models\ConversationSetting;
use App\Models\Message;
use App\Services\ChatService;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message) {}

    public function broadcastOn(): array
    {
        $conversation = $this->message->conversation;
        $recipientId = (int) $conversation->first_user_id === (int) $this->message->sender_id
            ? $conversation->second_user_id
            : $conversation->first_user_id;

        $recipientDeletedConversation = ConversationSetting::query()
            ->where('conversation_id', $this->message->conversation_id)
            ->where('user_id', $recipientId)
            ->whereNotNull('deleted_at')
            ->exists();

        // The sender has already rendered their own message locally. Deliver
        // only to the recipient's personal channel, and never resurrect a
        // conversation that recipient explicitly deleted.
        return $recipientDeletedConversation
            ? []
            : [new PrivateChannel("App.Models.User.{$recipientId}")];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'message' => app(ChatService::class)->messagePayload($this->message),
        ];
    }
}
