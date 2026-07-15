<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\EmployerDocument;
use App\Models\User;
use App\Events\JobPosted;
use App\Events\ProfileUpdated;
use App\Services\EmployerDocumentService;
use App\Services\RealtimeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EmployerController extends Controller
{
    public function __construct(
        private readonly EmployerDocumentService $documents,
        private readonly RealtimeService $realtime,
    ) {
    }

    public function dashboard(Request $request): View
    {
        /** @var User $employer */
        $employer = $request->user();
        $this->ensureEmployer($employer);
        $documentStatus = $this->documents->refreshStatus($employer);

        return view('employer.dashboard', [
            'employer' => $employer,
            'documentStatus' => $documentStatus,
            'jobs' => Job::query()
                ->where('user_id', $employer->id)
                ->withCount('applications')
                ->latest()
                ->get(),
        ]);
    }

    public function storeJob(Request $request): JsonResponse|RedirectResponse
    {
        /** @var User $employer */
        $employer = $request->user();
        $this->ensureEmployer($employer);

        abort_if(
            $this->documents->refreshStatus($employer) !== 'valid',
            422,
            'Job posting is unavailable while a required business document is expired or missing.'
        );

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'location' => ['required', 'string', 'max:160'],
            'employment_type' => ['required', 'in:full_time,part_time,contract,internship'],
            'vacancies' => ['required', 'integer', 'min:1', 'max:999'],
            'salary_min' => ['nullable', 'integer', 'min:0'],
            'salary_max' => ['nullable', 'integer', 'gte:salary_min'],
            'description' => ['required', 'string', 'max:5000'],
            'accommodations' => ['nullable', 'string', 'max:2000'],
            'application_requirements' => ['nullable', 'string', 'max:3000'],
        ]);

        $job = Job::create($validated + [
            'user_id' => $employer->id,
            'status' => 'published',
        ]);

        $this->realtime->broadcast(new JobPosted($job));

        $payload = [
            'job' => [
                'id' => $job->id,
                'title' => $job->title,
                'location' => $job->location,
                'employment_type' => str_replace('_', ' ', $job->employment_type),
                'vacancies' => $job->vacancies,
                'status' => $job->status,
            ],
        ];

        if ($request->expectsJson()) {
            return response()->json($payload, 201);
        }

        return redirect()->route('employer.dashboard')->with('status', 'Job post created.');
    }

    public function profile(Request $request): View
    {
        /** @var User $employer */
        $employer = $request->user();
        $this->ensureEmployer($employer);

        return view('employer.profile', [
            'employer' => $employer,
            'documentStatus' => $this->documents->refreshStatus($employer),
            'documents' => $employer->employerDocuments()->orderBy('document_type')->get(),
        ]);
    }

    public function updateProfile(Request $request): JsonResponse|RedirectResponse
    {
        /** @var User $employer */
        $employer = $request->user();
        $this->ensureEmployer($employer);

        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:160'],
            'employer_contact_name' => ['required', 'string', 'max:120', 'not_regex:/[0-9]/'],
            'employer_contact_number' => ['required', 'regex:/^\d{10}$/'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], [
            'employer_contact_number.regex' => 'Contact number must contain exactly 10 digits.',
        ]);

        $profilePhoto = $validated['profile_photo'] ?? null;
        unset($validated['profile_photo']);

        $employer->fill($validated);
        $employer->name = $validated['company_name'];

        if ($profilePhoto) {
            $previousPhotoPath = $employer->profile_photo_path;
            $employer->profile_photo_path = $profilePhoto->store('profile-photos', 'public');

            if ($previousPhotoPath) {
                Storage::disk('public')->delete($previousPhotoPath);
            }
        }

        $employer->save();
        $this->realtime->broadcast(new ProfileUpdated($employer));

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Business profile updated.',
                'profile' => [
                    'company_name' => $employer->company_name,
                    'employer_contact_name' => $employer->employer_contact_name,
                    'employer_contact_number' => $employer->employer_contact_number,
                    'photo_url' => $employer->profile_photo_url,
                ],
            ]);
        }

        return redirect()->route('employer.profile')->with('status', 'Business profile updated.');
    }

    public function renewDocument(Request $request, EmployerDocument $document): JsonResponse|RedirectResponse
    {
        /** @var User $employer */
        $employer = $request->user();
        $this->ensureEmployer($employer);
        abort_unless($document->user_id === $employer->id, 404);

        $validated = $request->validate([
            'document' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ]);

        $document->update([
            'file_path' => $validated['document']->store('employer-documents', 'local'),
            'expires_at' => now()->addDays((int) config('employer_documents.validity_days', 365)),
            'status' => 'valid',
        ]);
        $status = $this->documents->refreshStatus($employer);

        $message = $status === 'valid'
            ? 'Document renewed. Your job-posting access is active again.'
            : 'Document renewed. Renew the remaining expired documents to restore job-posting access.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'document_status' => $status,
            ]);
        }

        return redirect()
            ->route('employer.profile')
            ->with('status', $message);
    }

    private function ensureEmployer(User $user): void
    {
        abort_unless($user->account_type === 'employer', 403);
    }
}
