<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AccountReviewUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public User $account)
    {
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('admin.accounts')];
    }

    public function broadcastAs(): string
    {
        return 'account.review-updated';
    }

    public function broadcastWith(): array
    {
        return [
            'account' => AccountRegistered::accountPayload($this->account),
            'pending_verifications' => AccountRegistered::pendingVerificationCount(),
            'counts' => AccountRegistered::accountCounts(),
        ];
    }
}
