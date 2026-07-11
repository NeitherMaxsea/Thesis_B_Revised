<?php

namespace App\Events;

use App\Models\Conversation;
use App\Models\JobApplication;
use App\Services\ChatService;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobApplicationSubmitted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public JobApplication $application,
        public Conversation $conversation,
    ) {}

    public function broadcastOn(): array
    {
        $this->application->loadMissing('job');

        return [new PrivateChannel("App.Models.User.{$this->application->job->user_id}")];
    }

    public function broadcastAs(): string
    {
        return 'job-application.submitted';
    }

    public function broadcastWith(): array
    {
        $this->application->loadMissing(['applicant', 'job']);
        $chatService = app(ChatService::class);

        return [
            'application' => [
                'id' => $this->application->id,
                'job_id' => $this->application->job_id,
                'job_title' => $this->application->job->title,
                'applicant' => [
                    'id' => $this->application->applicant->id,
                    'name' => $chatService->displayName($this->application->applicant),
                    'initials' => $chatService->initials($this->application->applicant),
                ],
                'conversation_id' => $this->conversation->id,
                'conversation_url' => route('messages.index', ['conversation' => $this->conversation->id]),
                'applied_at' => $this->application->created_at?->toIso8601String(),
            ],
        ];
    }
}
