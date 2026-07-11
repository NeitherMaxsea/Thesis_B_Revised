<?php

namespace App\Http\Controllers;

use App\Events\JobApplicationSubmitted;
use App\Events\MessageSent;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use App\Services\ChatService;
use App\Services\EmployerDocumentService;
use App\Services\RealtimeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobApplicationController extends Controller
{
    public function store(
        Request $request,
        Job $job,
        ChatService $chatService,
        EmployerDocumentService $documents,
        RealtimeService $realtime,
    ): RedirectResponse {
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

        [$application, $conversation, $requirementsMessage, $wasCreated] = DB::transaction(
            function () use ($job, $applicant, $employer, $chatService) {
                $application = JobApplication::firstOrCreate([
                    'job_id' => $job->id,
                    'applicant_id' => $applicant->id,
                ], ['status' => 'applied']);

                $wasCreated = $application->wasRecentlyCreated;
                $conversation = $chatService->openApplicationConversation($application);
                $requirementsMessage = null;

                if ($wasCreated) {
                    $requirements = trim($job->application_requirements
                        ?: 'Please review the job description and wait for the employer’s next instruction.');
                    $requirementsMessage = $chatService->send(
                        $employer,
                        $conversation,
                        "Application requirements for {$job->title}:\n{$requirements}"
                    );
                }

                return [$application, $conversation, $requirementsMessage, $wasCreated];
            }
        );

        // The database work is complete before Reverb is contacted, so a
        // temporary WebSocket outage can never roll back an application.
        if ($requirementsMessage !== null) {
            $realtime->broadcast(new MessageSent($requirementsMessage));
        }

        if ($wasCreated) {
            $realtime->broadcast(new JobApplicationSubmitted($application, $conversation));
        }

        return redirect()
            ->route('messages.index', ['conversation' => $conversation->id])
            ->with('status', $wasCreated
                ? 'Application submitted. You can now message the employer securely.'
                : 'You already applied for this job. Continue the secure conversation here.');
    }
}
