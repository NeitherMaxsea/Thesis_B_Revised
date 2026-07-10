<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProfileUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public User $user)
    {
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel("App.Models.User.{$this->user->id}")];
    }

    public function broadcastAs(): string
    {
        return 'profile.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'profile' => [
                'name' => $this->user->first_name ?: $this->user->name,
                'full_name' => $this->user->name,
                'disability' => $this->user->disability ?: 'PWD Applicant',
                'email' => $this->user->email,
                'contact_number' => $this->user->contact_number,
                'street_address' => $this->user->street_address,
                'city' => $this->user->city,
            ],
        ];
    }
}
