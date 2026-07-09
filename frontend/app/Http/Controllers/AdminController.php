<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        $this->ensureAdmin();

        $applicants = User::query()
            ->where('account_type', 'pwd_applicant')
            ->latest()
            ->get();

        $employers = User::query()
            ->where('account_type', 'employer')
            ->latest()
            ->get();

        return view('admin.dashboard', [
            'applicants' => $applicants,
            'employers' => $employers,
            'stats' => [
                'applicants' => $applicants->count(),
                'pending' => $applicants->where('applicant_review_status', 'pending')->count(),
                'approved' => $applicants->where('applicant_review_status', 'approved')->count(),
                'declined' => $applicants->where('applicant_review_status', 'declined')->count(),
                'employers' => $employers->count(),
            ],
        ]);
    }

    public function approve(User $user): RedirectResponse
    {
        $this->ensureAdmin();
        $this->ensureApplicant($user);

        $user->forceFill([
            'applicant_review_status' => 'approved',
            'applicant_review_notes' => null,
            'applicant_reviewed_at' => now(),
        ])->save();

        return back()->with('status', "{$user->name} has been approved.");
    }

    public function decline(Request $request, User $user): RedirectResponse
    {
        $this->ensureAdmin();
        $this->ensureApplicant($user);

        $validated = $request->validate([
            'applicant_review_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $user->forceFill([
            'applicant_review_status' => 'declined',
            'applicant_review_notes' => ($validated['applicant_review_notes'] ?? null) ?: 'PWD ID is missing, unclear, or invalid.',
            'applicant_reviewed_at' => now(),
        ])->save();

        return back()->with('status', "{$user->name} has been declined.");
    }

    private function ensureAdmin(): void
    {
        abort_unless(auth()->user()?->account_type === 'admin', 403);
    }

    private function ensureApplicant(User $user): void
    {
        abort_unless($user->account_type === 'pwd_applicant', 404);
    }
}
