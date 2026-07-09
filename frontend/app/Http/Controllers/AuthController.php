<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class AuthController extends Controller
{
    private const DASMA_ADDRESSES = [
        'Burol Main',
        'Burol I',
        'Burol II',
        'Burol III',
        'Datu Esmael (Bago-A-Ingud)',
        'Emmanuel Bergado I',
        'Emmanuel Bergado II',
        'Fatima I',
        'Fatima II',
        'Fatima III',
        'Barangay H-2 (Santa Veronica)',
        'Langkaan I (Humayao)',
        'Langkaan II',
        'Luzviminda I',
        'Luzviminda II',
        'Paliparan I',
        'Paliparan II',
        'Paliparan III',
        'Sabang',
        'Salawag',
        'Saint Peter I',
        'Saint Peter II',
        'Salitran I',
        'Salitran II',
        'Salitran III',
        'Salitran IV',
        'Sampaloc I (Pala-Pala)',
        'Sampaloc II (Bucal/Malinta)',
        'Sampaloc III (Piela)',
        'Sampaloc IV (Talisayan/Bautista)',
        'Sampaloc V (New Era)',
        'San Agustin I',
        'San Agustin II (R. Tirona)',
        'San Agustin III',
        'San Andres I',
        'San Andres II',
        'San Antonio De Padua I',
        'San Antonio De Padua II',
        'San Dionisio',
        'San Esteban',
        'San Francisco I',
        'San Francisco II',
        'San Isidro Labrador I',
        'San Isidro Labrador II',
        'San Jose',
        'San Juan',
        'San Lorenzo Ruiz I',
        'San Lorenzo Ruiz II',
        'San Luis I',
        'San Luis II',
        'San Manuel I',
        'San Manuel II',
        'San Mateo',
        'San Miguel I',
        'San Miguel II',
        'San Nicolas I',
        'San Nicolas II',
        'San Roque',
        'San Simon',
        'Santa Cristina I',
        'Santa Cristina II',
        'Santa Cruz I',
        'Santa Cruz II',
        'Santa Fe',
        'Santa Lucia',
        'Santa Maria',
        'Santo Cristo',
        'Santo Nino I',
        'Santo Nino II',
        'Victoria Reyes',
        'Zone I-B',
        'Zone I',
        'Zone II',
        'Zone III',
        'Zone IV',
    ];

    private const NAME_SUFFIXES = ['Jr.', 'Sr.', 'II', 'III', 'IV'];

    public function login(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('login')
                ->withErrors($validator)
                ->withInput($request->only('email'));
        }

        $credentials = $validator->validated();

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Invalid email or password.'])
                ->onlyInput('email');
        }

        $user = $request->user();

        if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
            Auth::logout();

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Please verify your email before logging in.'])
                ->withInput($request->only('email'));
        }

        $request->session()->regenerate();

        if ($user->account_type === 'admin') {
            return redirect()->intended(route('admin.dashboard'));
        }

        if ($user->account_type === 'pwd_applicant') {
            $targetRoute = $user->applicant_review_status === 'approved'
                ? route('applicant.dashboard')
                : route('applicant.review');

            return redirect()->intended($targetRoute);
        }

        return redirect()->intended('/');
    }

    public function register(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'first_name' => ['required_if:account_type,pwd_applicant', 'nullable', 'string', 'max:120', 'not_regex:/[0-9]/'],
            'last_name' => ['required_if:account_type,pwd_applicant', 'nullable', 'string', 'max:120', 'not_regex:/[0-9]/'],
            'suffix' => ['nullable', Rule::in(self::NAME_SUFFIXES)],
            'gender' => ['required_if:account_type,pwd_applicant', 'nullable', Rule::in(['female', 'male', 'non_binary', 'prefer_not_to_say'])],
            'age' => ['required_if:account_type,pwd_applicant', 'nullable', 'integer', 'min:15', 'max:100'],
            'birthdate' => ['required_if:account_type,pwd_applicant', 'nullable', 'date', 'before:today'],
            'disability' => ['required_if:account_type,pwd_applicant', 'nullable', 'string', 'max:160'],
            'contact_number' => ['required_if:account_type,pwd_applicant', 'nullable', 'string', 'max:30'],
            'street_address' => ['required_if:account_type,pwd_applicant', 'nullable', 'string', 'max:255', Rule::in(self::DASMA_ADDRESSES)],
            'city' => ['nullable', Rule::in(['Dasmarinas'])],
            'latitude' => ['nullable', 'numeric', 'between:14.20,14.40'],
            'longitude' => ['nullable', 'numeric', 'between:120.85,121.05'],
            'pwd_id' => ['required_if:account_type,pwd_applicant', 'nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'final_confirmation' => ['required_if:account_type,pwd_applicant', 'accepted'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'account_type' => ['required', Rule::in(['pwd_applicant', 'employer'])],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'first_name.not_regex' => 'First name must not contain numbers.',
            'last_name.not_regex' => 'Last name must not contain numbers.',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('register')
                ->withErrors($validator)
                ->withInput($request->except('password'));
        }

        $validated = $validator->validated();
        $applicantNameParts = array_filter([
            $validated['first_name'] ?? null,
            $validated['last_name'] ?? null,
            $validated['suffix'] ?? null,
        ], fn ($part) => filled($part));

        $validated['name'] = $validated['account_type'] === 'pwd_applicant'
            ? implode(' ', $applicantNameParts)
            : strtok($validated['email'], '@');

        if ($validated['account_type'] === 'pwd_applicant') {
            $validated['city'] = 'Dasmarinas';
            $validated['applicant_review_status'] = 'pending';

            if ($request->hasFile('pwd_id')) {
                $validated['pwd_id_path'] = $request->file('pwd_id')->store('pwd-verifications', 'public');
            }
        } else {
            $validated['applicant_review_status'] = 'approved';

            unset(
                $validated['first_name'],
                $validated['last_name'],
                $validated['suffix'],
                $validated['gender'],
                $validated['age'],
                $validated['birthdate'],
                $validated['disability'],
                $validated['contact_number'],
                $validated['street_address'],
                $validated['city'],
                $validated['latitude'],
                $validated['longitude']
            );
        }

        unset($validated['pwd_id'], $validated['final_confirmation'], $validated['password_confirmation']);

        $user = User::create($validated);
        $verificationSent = $this->sendVerificationEmail($user);

        return redirect()
            ->route('register.verification')
            ->with([
                'verification_email' => $user->email,
                'verification_sent' => $verificationSent,
            ]);
    }

    private function sendVerificationEmail(User $user): bool
    {
        if (config('mail.default') === 'smtp') {
            $username = (string) config('mail.mailers.smtp.username');
            $password = (string) config('mail.mailers.smtp.password');

            if (
                blank($username) ||
                blank($password) ||
                str_contains($username, 'your-gmail-address') ||
                str_contains($password, 'your-gmail-app-password')
            ) {
                return false;
            }
        }

        try {
            $user->sendEmailVerificationNotification();

            return true;
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
