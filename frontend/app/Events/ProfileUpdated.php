<?php

namespace App\Events;

use App\Models\JobApplication;
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
        $channels = [new PrivateChannel("App.Models.User.{$this->user->id}")];

        if ($this->user->account_type !== 'pwd_applicant') {
            return $channels;
        }

        // Only admins and employers already connected through an application
        // are told about an applicant's new profile image.
        $channels[] = new PrivateChannel('admin.accounts');
        $employerIds = JobApplication::query()
            ->where('applicant_id', $this->user->id)
            ->join('jobs', 'jobs.id', '=', 'job_applications.job_id')
            ->pluck('jobs.user_id')
            ->unique();

        foreach ($employerIds as $employerId) {
            $channels[] = new PrivateChannel("App.Models.User.{$employerId}");
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'profile.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->user->id,
            'profile' => [
                'name' => $this->user->first_name ?: $this->user->name,
                'full_name' => $this->user->name,
                'disability' => $this->user->disability_display ?: 'PWD Applicant',
                'general_disability_category' => $this->user->disability,
                'disability_category' => $this->user->disability_category,
                'email' => $this->user->email,
                'contact_number' => $this->user->contact_number,
                'street_address' => $this->user->street_address,
                'city' => $this->user->city,
                'photo_url' => $this->user->profile_photo_url,
            ],
        ];
    }
}
