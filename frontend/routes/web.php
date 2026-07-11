<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApplicantProfileController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\EmployerController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\JobMatchController;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::view('/', 'main')->name('home');

Route::redirect('/home', '/')->name('legacy.home');
Route::redirect('/jobs', '/#jobs')->name('jobs');
Route::redirect('/faq', '/#faq')->name('faq');

$informationPages = [
    'about' => [
        'eyebrow' => 'About Us',
        'title' => 'Inclusive employment built around accessibility',
        'description' => 'The PWD Job Employment Assistance Platform helps Persons with Disabilities discover inclusive opportunities and connect with employers committed to accessible hiring.',
        'items' => [
            ['title' => 'For applicants', 'description' => 'Create a verified profile, browse suitable opportunities, and follow application updates in one place.'],
            ['title' => 'For employers', 'description' => 'Connect with qualified applicants and support a more inclusive workforce.'],
            ['title' => 'Accessible by design', 'description' => 'Built-in display, reading, language, and voice controls help more people use the platform comfortably.'],
        ],
    ],
    'contact' => [
        'eyebrow' => 'Contact Us',
        'title' => 'We are here to help',
        'description' => 'For account, verification, or application concerns, contact the platform administrator who provided access to this system. Official public support details will be posted here when they are available.',
        'items' => [
            ['title' => 'Account support', 'description' => 'Include the email address used for registration and a short description of the issue. Never send your password.'],
            ['title' => 'Application support', 'description' => 'Include the job or application concerned so the support team can locate it quickly.'],
        ],
    ],
    'tutorial' => [
        'eyebrow' => 'Tutorial',
        'title' => 'Get started with the platform',
        'description' => 'Use these quick guides to create an account and begin using the employment system.',
        'items' => [
            ['id' => 'register-applicant', 'title' => 'Register as an applicant', 'description' => 'Choose Create an Account, select PWD Applicant, complete each registration step, upload your PWD ID, and verify your email.'],
            ['id' => 'register-employer', 'title' => 'Register as an employer', 'description' => 'Choose Create an Account, select Employer, enter your account details, and verify your email before signing in.'],
            ['id' => 'apply-work', 'title' => 'Apply for work', 'description' => 'After your applicant account is approved, sign in, complete your profile, browse available jobs, and submit the requested application information.'],
        ],
    ],
    'resources' => [
        'eyebrow' => 'Resources',
        'title' => 'Employment and accessibility resources',
        'description' => 'Guides and partner resources for applicants and inclusive employers will be collected on this page as they become available.',
        'items' => [
            ['title' => 'Applicant preparation', 'description' => 'Prepare an updated resume, valid contact information, and any documents requested by an employer.'],
            ['title' => 'Inclusive hiring', 'description' => 'Employers can review job requirements, workplace accessibility, and reasonable accommodations before publishing a role.'],
        ],
    ],
    'accessibility-policy' => [
        'eyebrow' => 'Accessibility Policy',
        'title' => 'Access for every user',
        'description' => 'This platform aims to provide keyboard-friendly navigation, readable content, adjustable display settings, and assistive accessibility controls. Report any barrier through the Contact Us page so it can be reviewed.',
        'items' => [],
    ],
    'terms' => [
        'eyebrow' => 'Terms and Conditions',
        'title' => 'Using this platform responsibly',
        'description' => 'Users must provide accurate account information, keep login credentials secure, respect other users, and use applicant and employer data only for legitimate employment-related purposes. Full production terms will be published before public deployment.',
        'items' => [],
    ],
    'privacy' => [
        'eyebrow' => 'Privacy Policy',
        'title' => 'Your information matters',
        'description' => 'Registration and verification information is collected to operate applicant and employer accounts. Personal information should be accessed only by authorized users and used only for platform administration and employment services. Full production privacy details will be published before public deployment.',
        'items' => [],
    ],
    'help' => [
        'eyebrow' => 'Help Center',
        'title' => 'Find help with common tasks',
        'description' => 'Start with the tutorial and frequently asked questions. If the issue continues, use the Contact Us page and describe the screen, action, and message you encountered.',
        'items' => [
            ['title' => 'Cannot sign in', 'description' => 'Confirm that your email is verified and that you are using the same email address entered during registration.'],
            ['title' => 'Verification link issue', 'description' => 'Request a fresh verification link from the verification screen; older signed links may have expired.'],
            ['title' => 'Application status', 'description' => 'Sign in to your applicant dashboard to see the latest review or application update.'],
        ],
    ],
];

foreach ($informationPages as $slug => $page) {
    Route::view("/{$slug}", 'pages.information', ['page' => $page])
        ->name("pages.{$slug}");
}

Route::get('/login', function () {
    return view('auth.login');
})->middleware('guest')->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->middleware('guest')->name('register');

Route::get('/register/verification', function () {
    return view('auth.register-verification');
})->middleware('guest')->name('register.verification');

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('guest')
    ->name('login.store');

Route::post('/register', [AuthController::class, 'register'])
    ->middleware('guest')
    ->name('register.store');

Route::post('/email/verification-notification', [AuthController::class, 'resendVerificationEmail'])
    ->middleware(['guest', 'throttle:6,1'])
    ->name('verification.send');

Route::get('/email/verify', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()
        ->route('login')
        ->withErrors(['email' => 'Please verify your email before logging in.']);
})->middleware('auth')->name('verification.notice');

// EMAIL VERIFICATION: users are taken directly to the appropriate safe next step after confirmation.
Route::get('/email/verify/{id}/{hash}', function (Request $request, string $id, string $hash) {
    $user = User::findOrFail($id);

    abort_unless(hash_equals((string) $hash, sha1($user->getEmailForVerification())), 403);

    if (! $user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
        event(new Verified($user));
    }

    Auth::login($user);
    $request->session()->regenerate();

    return view('auth.email-verified', [
        'redirectUrl' => $user->account_type === 'pwd_applicant'
            ? route('applicant.review')
            : route('employer.dashboard'),
    ]);
})->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

// APPLICANT ONLY: use these routes and dashboard views for PWD applicant changes.
Route::get('/applicant/review', function () {
    if (auth()->user()?->account_type !== 'pwd_applicant') {
        return redirect('/');
    }

    return view('dashboard.applicant-review');
})->middleware(['auth', 'verified'])->name('applicant.review');

Route::get('/applicant/dashboard', [JobMatchController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('applicant.dashboard');

Route::post('/jobs/{job}/apply', [JobApplicationController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('jobs.apply');

Route::get('/applicant/profile', [ApplicantProfileController::class, 'show'])
    ->middleware(['auth', 'verified'])
    ->name('applicant.profile');

Route::patch('/applicant/profile', [ApplicantProfileController::class, 'update'])
    ->middleware(['auth', 'verified'])
    ->name('applicant.profile.update');

Route::get('/employer/dashboard', [EmployerController::class, 'dashboard'])
    ->middleware(['auth', 'verified'])
    ->name('employer.dashboard');

Route::post('/employer/jobs', [EmployerController::class, 'storeJob'])
    ->middleware(['auth', 'verified'])
    ->name('employer.jobs.store');

Route::post('/employer/documents/{document}/renew', [EmployerController::class, 'renewDocument'])
    ->middleware(['auth', 'verified'])
    ->name('employer.documents.renew');

Route::get('/employer/profile', [EmployerController::class, 'profile'])
    ->middleware(['auth', 'verified'])
    ->name('employer.profile');

Route::patch('/employer/profile', [EmployerController::class, 'updateProfile'])
    ->middleware(['auth', 'verified'])
    ->name('employer.profile.update');

Route::get('/messages', [ChatController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('messages.index');

Route::post('/messages/conversations', [ChatController::class, 'start'])
    ->middleware(['auth', 'verified'])
    ->name('messages.conversations.store');

Route::post('/messages/{conversation}', [ChatController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('messages.store');

Route::patch('/messages/{conversation}/read', [ChatController::class, 'markRead'])
    ->middleware(['auth', 'verified'])
    ->name('messages.read');

Route::get('/messages/{conversation}/presence', [ChatController::class, 'presence'])
    ->middleware(['auth', 'verified'])
    ->name('messages.presence');

Route::post('/activity', function (Request $request) {
    $request->user()?->forceFill(['last_seen_at' => now()])->save();

    return response()->noContent();
})->middleware('auth')->name('activity');

// ADMIN ONLY: AdminController performs the second role check before showing or changing reviews.
Route::get('/admin/dashboard', [AdminController::class, 'index'])
    ->middleware('auth')
    ->name('admin.dashboard');

Route::get('/admin/users', [AdminController::class, 'users'])
    ->middleware('auth')
    ->name('admin.users.index');

Route::get('/admin/users/create', [AdminController::class, 'createUser'])
    ->middleware('auth')
    ->name('admin.users.create');

Route::post('/admin/users', [AdminController::class, 'storeUser'])
    ->middleware('auth')
    ->name('admin.users.store');

Route::get('/admin/applicants', [AdminController::class, 'applicants'])
    ->middleware('auth')
    ->name('admin.applicants.index');

Route::get('/admin/applicants/{user}/pwd-id', [AdminController::class, 'downloadApplicantPwdId'])
    ->middleware('auth')
    ->name('admin.applicants.pwd-id');

Route::patch('/admin/applicants/{user}', [AdminController::class, 'updateApplicant'])
    ->middleware('auth')
    ->name('admin.applicants.update');

Route::get('/admin/notifications', [AdminController::class, 'notifications'])
    ->middleware('auth')
    ->name('admin.notifications');

Route::post('/admin/applicants/{user}/approve', [AdminController::class, 'approve'])
    ->middleware('auth')
    ->name('admin.applicants.approve');

Route::post('/admin/applicants/{user}/decline', [AdminController::class, 'decline'])
    ->middleware('auth')
    ->name('admin.applicants.decline');

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');
