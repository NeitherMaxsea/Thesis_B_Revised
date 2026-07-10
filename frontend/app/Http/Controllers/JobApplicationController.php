<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use App\Events\MessageSent;
use App\Services\ChatService;
use App\Services\EmployerDocumentService;
use App\Services\RealtimeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class JobApplicationController extends Controller
{
    public function store(
        Request $request,
        Job $job,
        ChatService $chatService,
        EmployerDocumentService $documents,
        RealtimeService $realtime,
    ): RedirectResponse
    {
        /** @var User $applicant */
        $applicant = $request->user();
        abort_unless(
            $applicant->account_type === 'pwd_applicant' && $applicant->applicant_review_status === 'approved',
            403
        );
        abort_unless($job->status === 'published', 404);

        $employer = $job->employer;
        abort_unless(
            $employer instanceof User && $documents->refreshStatus($employer) === 'valid',
            404
        );

        $application = JobApplication::firstOrCreate([
            'job_id' => $job->id,
            'applicant_id' => $applicant->id,
        ], ['status' => 'applied']);

        $conversation = $chatService->openConversation($applicant, $employer);

        if ($application->wasRecentlyCreated) {
            $requirements = trim($job->application_requirements ?: 'Please review the job description and wait for the employer’s next instruction.');
            $requirementsMessage = $chatService->send(
                $job->employer,
                $conversation,
                "Application requirements for {$job->title}:\n{$requirements}"
            );

            $realtime->broadcast(new MessageSent($requirementsMessage));
        }

        return redirect()
            ->route('messages.index', ['conversation' => $conversation->id])
            ->with('status', $application->wasRecentlyCreated
                ? 'Application submitted. You can now message the employer securely.'
                : 'You already applied for this job. Continue the secure conversation here.');
    }
}
