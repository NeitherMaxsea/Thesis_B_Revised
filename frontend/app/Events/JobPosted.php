<?php

namespace App\Events;

use App\Models\Job;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobPosted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Job $job)
    {
    }

    public function broadcastOn(): array
    {
        return [new Channel('jobs')];
    }

    public function broadcastAs(): string
    {
        return 'job.posted';
    }

    public function broadcastWith(): array
    {
        return [
            'job' => [
                'id' => $this->job->id,
                'title' => $this->job->title,
                'location' => $this->job->location,
            ],
        ];
    }
}
