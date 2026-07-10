<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;

class AdminController extends Controller
{
    // ADMIN ONLY: this is the data source for the review queue and employer list.
    // Change the queries here when the admin dashboard needs different information.
    public function index(): View
    {
        $this->ensureAdmin();

        // PWD applicants require a manual decision; keep this separate from employer accounts.
        $applicants = User::query()
            ->where('account_type', 'pwd_applicant')
            ->orderByRaw("CASE applicant_review_status WHEN 'pending' THEN 0 WHEN 'declined' THEN 1 ELSE 2 END")
            ->latest()
            ->get();

        // Employer accounts are displayed for reference only and are not reviewed here.
        $employers = User::query()
            ->where('account_type', 'employer')
            ->latest()
            ->get();

        $pendingApplicants = $applicants
            ->where('applicant_review_status', 'pending')
            ->values();
        $approvedApplicants = $applicants
            ->where('applicant_review_status', 'approved')
            ->values();
        $reviewedApplicants = $applicants
            ->where('applicant_review_status', '!=', 'pending')
            ->values();

        // Dashboard rows combine approved PWD applicants with employer accounts. Employer
        // accounts do not use the applicant-review state, so they are shown as active records.
        $recentApproved = $approvedApplicants
            ->map(fn (User $user) => [
                'user' => $user,
                'role' => 'Applicant',
                'approved_at' => $user->applicant_reviewed_at ?? $user->updated_at ?? $user->created_at,
            ])
            ->concat($employers->map(fn (User $user) => [
                'user' => $user,
                'role' => 'Business',
                'approved_at' => $user->updated_at ?? $user->created_at,
            ]))
            ->sortByDesc('approved_at')
            ->take(5)
            ->values();

        $profileTotal = $pendingApplicants->count() + $reviewedApplicants->count() + $employers->count();
        $trendStart = now()->startOfMonth()->subMonths(5);
        $accountTrends = collect(range(0, 5))->map(function (int $offset) use ($trendStart, $applicants, $employers) {
            $monthStart = $trendStart->copy()->addMonths($offset)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();

            return [
                'label' => $monthStart->format('M Y'),
                'applicants' => $applicants->filter(
                    fn (User $user) => $user->created_at?->betweenIncluded($monthStart, $monthEnd)
                )->count(),
                'employers' => $employers->filter(
                    fn (User $user) => $user->created_at?->betweenIncluded($monthStart, $monthEnd)
                )->count(),
            ];
        })->values();
        $latestApplicantCreatedAt = $applicants->sortByDesc('created_at')->first()?->created_at;

        return view('admin.dashboard', [
            'applicants' => $applicants,
            'employers' => $employers,
            'recentApproved' => $recentApproved,
            'accountTrends' => $accountTrends,
            'latestApplicantCreatedAt' => $latestApplicantCreatedAt,
            'stats' => [
                'applicants' => $applicants->count(),
                'pending' => $pendingApplicants->count(),
                'approved' => $approvedApplicants->count(),
                'declined' => $applicants->where('applicant_review_status', 'declined')->count(),
                'employers' => $employers->count(),
                'profiles' => $profileTotal,
                'active' => $approvedApplicants->count() + $employers->count(),
                'inactive' => 0,
                'jobs' => 0,
                'deleted' => 0,
            ],
        ]);
    }

    public function users(Request $request): View
    {
        $this->ensureAdmin();

        $initialRole = $request->string('role')->lower()->value();
        $accountType = match ($initialRole) {
            'applicant' => 'pwd_applicant',
            'business' => 'employer',
            default => null,
        };

        $users = User::query()
            ->whereIn('account_type', ['pwd_applicant', 'employer'])
            ->when($accountType, fn ($query) => $query->where('account_type', $accountType))
            ->latest()
            ->get();

        return view('admin.users-overview', [
            'users' => $users,
            'initialRole' => $initialRole,
        ]);
    }

    public function createUser(): View
    {
        $this->ensureAdmin();

        return view('admin.create-user');
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $this->ensureAdmin();

        $accountType = $request->input('account_type');
        $rules = [
            'account_type' => ['required', Rule::in(['pwd_applicant', 'employer'])],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8'],
        ];

        if ($accountType === 'pwd_applicant') {
            $rules += [
                'first_name' => ['required', 'string', 'max:120'],
                'last_name' => ['required', 'string', 'max:120'],
                'disability' => ['required', 'string', 'max:160'],
                'street_address' => ['required', 'string', 'max:255'],
                'contact_number' => ['required', 'string', 'max:30'],
                'birthdate' => ['required', 'date', 'before:today'],
                'age' => ['required', 'integer', 'min:15', 'max:100'],
                'pwd_id' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            ];
        } else {
            $rules += [
                'business_name' => ['required', 'string', 'max:255'],
                'industry' => ['required', 'string', 'max:120'],
                'contact_number' => ['required', 'string', 'max:30'],
            ];
        }

        $validated = $request->validate($rules);
        $isApplicant = $validated['account_type'] === 'pwd_applicant';
        $userData = [
            'name' => $isApplicant
                ? trim("{$validated['first_name']} {$validated['last_name']}")
                : $validated['business_name'],
            'email' => $validated['email'],
            'account_type' => $validated['account_type'],
            'password' => Hash::make($validated['password']),
            'applicant_review_status' => $isApplicant ? 'pending' : 'approved',
        ];

        if ($isApplicant) {
            $userData += [
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'disability' => $validated['disability'],
                'street_address' => $validated['street_address'],
                'city' => 'Dasmarinas',
                'contact_number' => $validated['contact_number'],
                'birthdate' => $validated['birthdate'],
                'age' => $validated['age'],
            ];

            if ($request->hasFile('pwd_id')) {
                $userData['pwd_id_path'] = $request->file('pwd_id')->store('pwd-verifications', 'local');
            }
        } else {
            $userData['contact_number'] = $validated['contact_number'];
        }

        $user = User::create($userData);
        $user->forceFill(['email_verified_at' => now()])->save();

        return redirect()
            ->route($isApplicant ? 'admin.applicants.index' : 'admin.users.index', $isApplicant ? [] : ['role' => 'business'])
            ->with('status', "{$user->name} has been created.");
    }

    public function applicants(): View
    {
        $this->ensureAdmin();

        $applicants = User::query()
            ->where('account_type', 'pwd_applicant')
            ->latest()
            ->get();

        return view('admin.applicants', compact('applicants'));
    }

    public function downloadApplicantPwdId(User $user)
    {
        $this->ensureAdmin();
        $this->ensureApplicant($user);
        abort_unless(filled($user->pwd_id_path), 404);

        // New uploads are private. The public fallback lets existing local data
        // remain reviewable while it is moved out of the old public directory.
        $disk = Storage::disk('local')->exists($user->pwd_id_path) ? 'local' : 'public';
        abort_unless(Storage::disk($disk)->exists($user->pwd_id_path), 404);

        $extension = pathinfo($user->pwd_id_path, PATHINFO_EXTENSION);
        $filename = 'pwd-id-'.$user->id.($extension ? '.'.$extension : '');

        return Storage::disk($disk)->download($user->pwd_id_path, $filename);
    }

    public function updateApplicant(Request $request, User $user): JsonResponse
    {
        $this->ensureAdmin();
        $this->ensureApplicant($user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'contact_number' => ['nullable', 'string', 'max:30'],
            'disability' => ['nullable', 'string', 'max:160'],
            'age' => ['nullable', 'integer', 'min:15', 'max:100'],
            'birthdate' => ['nullable', 'date', 'before:today'],
            'street_address' => ['nullable', 'string', 'max:255'],
        ]);

        $user->fill($validated)->save();

        return response()->json([
            'message' => "{$user->name} has been updated.",
            'applicant' => [
                'name' => $user->name,
                'email' => $user->email,
                'contact' => $user->contact_number ?: 'Not set',
                'disability' => $user->disability ?: 'Not set',
                'age' => $user->age ?: 'Not set',
                'birthdate' => $user->birthdate?->format('Y-m-d') ?? 'Not set',
                'address' => $user->street_address ?: 'Not set',
            ],
        ]);
    }

    public function notifications(Request $request): JsonResponse
    {
        $this->ensureAdmin();

        $since = null;

        if ($request->filled('since')) {
            try {
                $since = Carbon::parse($request->string('since')->value());
            } catch (\Throwable) {
                $since = null;
            }
        }

        $accountQuery = User::query()->whereIn('account_type', ['pwd_applicant', 'employer']);
        $newCount = $since
            ? (clone $accountQuery)->where('created_at', '>', $since)->count()
            : 0;
        $notifications = $accountQuery
            ->latest()
            ->take(8)
            ->get()
            ->map(fn (User $user) => [
                'name' => $user->name,
                'role' => $user->account_type === 'employer' ? 'Business' : 'Applicant',
                'created_at' => $user->created_at?->format('M d, Y · g:i A'),
            ]);

        return response()->json([
            'now' => now()->toIso8601String(),
            'new_count' => $newCount,
            'notifications' => $notifications,
        ]);
    }

    public function approve(User $user): RedirectResponse
    {
        $this->ensureAdmin();
        $this->ensureApplicant($user);

        // ADMIN DECISION: approving unlocks the applicant dashboard and removes an old rejection note.
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

        // ADMIN DECISION: a reason is required because the applicant sees this note on their review page.
        $validated = $request->validate([
            'applicant_review_notes' => ['required', 'string', 'max:500'],
        ], [
            'applicant_review_notes.required' => 'Add a reason so the applicant knows what needs to be corrected.',
        ]);

        $user->forceFill([
            'applicant_review_status' => 'declined',
            'applicant_review_notes' => $validated['applicant_review_notes'],
            'applicant_reviewed_at' => now(),
        ])->save();

        return back()->with('status', "{$user->name} has been declined.");
    }

    private function ensureAdmin(): void
    {
        // Keep the role check here even though the route uses auth middleware.
        abort_unless(auth()->user()?->account_type === 'admin', 403);
    }

    private function ensureApplicant(User $user): void
    {
        // Prevent admin review actions from changing an employer or another admin account.
        abort_unless($user->account_type === 'pwd_applicant', 404);
    }
}
