<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
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

Route::get('/', function () {
    return view('main');
});

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

Route::get('/email/verify', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()
        ->route('login')
        ->withErrors(['email' => 'Please verify your email before logging in.']);
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (Request $request, string $id, string $hash) {
    $user = User::findOrFail($id);

    abort_unless(hash_equals((string) $hash, sha1($user->getEmailForVerification())), 403);

    if (! $user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
        event(new Verified($user));
    }

    if ($user->account_type === 'pwd_applicant') {
        Auth::login($user);
        $request->session()->regenerate();

        return view('auth.email-verified', [
            'redirectUrl' => route('applicant.review'),
        ]);
    }

    return view('auth.email-verified', [
        'redirectUrl' => route('login'),
    ]);
})->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

Route::get('/applicant/review', function () {
    if (auth()->user()?->account_type !== 'pwd_applicant') {
        return redirect('/');
    }

    return view('dashboard.applicant-review');
})->middleware(['auth', 'verified'])->name('applicant.review');

Route::get('/applicant/dashboard', function () {
    if (auth()->user()?->account_type !== 'pwd_applicant') {
        return redirect('/');
    }

    if (auth()->user()?->applicant_review_status !== 'approved') {
        return redirect()->route('applicant.review');
    }

    return view('dashboard.applicant');
})->middleware(['auth', 'verified'])->name('applicant.dashboard');

Route::get('/admin/dashboard', [AdminController::class, 'index'])
    ->middleware('auth')
    ->name('admin.dashboard');

Route::post('/admin/applicants/{user}/approve', [AdminController::class, 'approve'])
    ->middleware('auth')
    ->name('admin.applicants.approve');

Route::post('/admin/applicants/{user}/decline', [AdminController::class, 'decline'])
    ->middleware('auth')
    ->name('admin.applicants.decline');

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');
