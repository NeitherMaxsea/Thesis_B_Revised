<?php

namespace App\Http\Controllers;

use App\Events\AccountRegistered;
use App\Models\User;
use App\Models\EmployerDocument;
use App\Services\RealtimeService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
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
            $delivery = $this->sendVerificationEmail($user);

            Auth::logout();

            return redirect()
                ->route('register.verification')
                ->with($this->verificationFlashData($user->email, $delivery));
        }

        $request->session()->regenerate();

        // ROLE ROUTING: update these destinations when a role gets its own dashboard.
        if ($user->account_type === 'admin') {
            return redirect()->intended(route('admin.dashboard'));
        }

        if ($user->account_type === 'pwd_applicant') {
            $targetRoute = $user->applicant_review_status === 'approved'
                ? route('applicant.dashboard')
                : route('applicant.review');

            return redirect()->intended($targetRoute);
        }

        if ($user->account_type === 'employer') {
            // The destination dashboard refreshes document status once while it
            // loads. Avoid the same database work twice during login.
            return redirect()->intended(route('employer.dashboard'));
        }

        return redirect()->intended('/');
    }

    public function register(Request $request): RedirectResponse
    {
        $disabilityCategories = config('applicant.disability_categories', []);

        // APPLICANT RULES: the PWD profile and ID are mandatory only for pwd_applicant accounts.
        // EMPLOYER RULES: employers use only the shared account fields below.
        $validator = Validator::make($request->all(), [
            'first_name' => ['required_if:account_type,pwd_applicant', 'nullable', 'string', 'max:120', 'not_regex:/[0-9]/'],
            'last_name' => ['required_if:account_type,pwd_applicant', 'nullable', 'string', 'max:120', 'not_regex:/[0-9]/'],
            'suffix' => ['nullable', Rule::in(self::NAME_SUFFIXES)],
            'gender' => ['required_if:account_type,pwd_applicant', 'nullable', Rule::in(['female', 'male', 'non_binary', 'prefer_not_to_say'])],
            'age' => ['required_if:account_type,pwd_applicant', 'nullable', 'integer', 'min:15', 'max:100'],
            'birthdate' => ['required_if:account_type,pwd_applicant', 'nullable', 'date', 'before:today'],
            'disability' => ['required_if:account_type,pwd_applicant', 'nullable', Rule::in(array_keys($disabilityCategories))],
            'disability_category' => ['required_if:account_type,pwd_applicant', 'nullable', 'string', 'max:160'],
            'contact_number' => ['required_if:account_type,pwd_applicant', 'nullable', 'regex:/^\d{10}$/'],
            'street_address' => ['required_if:account_type,pwd_applicant', 'nullable', 'string', 'max:255', Rule::in(self::DASMA_ADDRESSES)],
            'city' => ['nullable', Rule::in(['Dasmarinas'])],
            'latitude' => ['nullable', 'numeric', 'between:14.20,14.40'],
            'longitude' => ['nullable', 'numeric', 'between:120.85,121.05'],
            'pwd_id' => ['required_if:account_type,pwd_applicant', 'nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'company_name' => ['required_if:account_type,employer', 'nullable', 'string', 'max:160'],
            'employer_contact_name' => ['required_if:account_type,employer', 'nullable', 'string', 'max:120', 'not_regex:/[0-9]/'],
            'employer_contact_number' => ['required_if:account_type,employer', 'nullable', 'regex:/^\d{10}$/'],
            'dole_certificate' => ['required_if:account_type,employer', 'nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'bir_certificate' => ['required_if:account_type,employer', 'nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'business_permit' => ['required_if:account_type,employer', 'nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'dti_certificate' => ['required_if:account_type,employer', 'nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'final_confirmation' => ['required', 'accepted'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'account_type' => ['required', Rule::in(['pwd_applicant', 'employer'])],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'first_name.not_regex' => 'First name must not contain numbers.',
            'last_name.not_regex' => 'Last name must not contain numbers.',
            'contact_number.regex' => 'Contact number must contain exactly 10 digits.',
            'employer_contact_number.regex' => 'Contact number must contain exactly 10 digits.',
        ]);

        $validator->after(function ($validator) use ($request, $disabilityCategories) {
            if ($request->input('account_type') !== 'pwd_applicant') {
                return;
            }

            $generalCategory = $request->input('disability');
            $specificCategory = $request->input('disability_category');

            if (! in_array($specificCategory, $disabilityCategories[$generalCategory] ?? [], true)) {
                $validator->errors()->add('disability_category', 'Choose a disability category under the selected general disability category.');
            }
        });

        if ($validator->fails()) {
            return redirect()
                ->route('register')
                ->withErrors($validator)
                ->withInput($request->except('password'));
        }

        $validated = $validator->validated();

        // APPLICANT DISPLAY NAME: built from the personal profile fields.
        // EMPLOYER DISPLAY NAME: uses the verified business name supplied in step one.
        $applicantNameParts = array_filter([
            $validated['first_name'] ?? null,
            $validated['last_name'] ?? null,
            $validated['suffix'] ?? null,
        ], fn ($part) => filled($part));

        $validated['name'] = $validated['account_type'] === 'pwd_applicant'
            ? implode(' ', $applicantNameParts)
            : $validated['company_name'];

        if ($validated['account_type'] === 'pwd_applicant') {
            // APPLICANT FLOW: queue the account for admin review and store its submitted PWD ID.
            $validated['city'] = 'Dasmarinas';
            $validated['applicant_review_status'] = 'pending';

            if ($request->hasFile('pwd_id')) {
                $validated['pwd_id_path'] = $request->file('pwd_id')->store('pwd-verifications', 'local');
            }
        } else {
            // EMPLOYER FLOW: four future-dated documents are required before job posting is allowed.
            $validated['applicant_review_status'] = 'approved';
            $validated['employer_document_status'] = 'valid';

            unset(
                $validated['first_name'],
                $validated['last_name'],
                $validated['suffix'],
                $validated['gender'],
                $validated['age'],
                $validated['birthdate'],
                $validated['disability'],
                $validated['disability_category'],
                $validated['contact_number'],
                $validated['street_address'],
                $validated['city'],
                $validated['latitude'],
                $validated['longitude']
            );
        }

        $employerDocumentTypes = [
            'dole_certificate' => 'DOLE Certificate',
            'bir_certificate' => 'BIR Certificate',
            'business_permit' => 'Business Permit',
            'dti_certificate' => 'DTI Certificate',
        ];
        $employerDocuments = [];

        if ($validated['account_type'] === 'employer') {
            $systemExpiryDate = now()->addDays((int) config('employer_documents.validity_days', 365))->toDateString();

            foreach ($employerDocumentTypes as $field => $documentType) {
                $employerDocuments[] = [
                    'document_type' => $documentType,
                    'file_path' => $request->file($field)->store('employer-documents', 'local'),
                    'expires_at' => $systemExpiryDate,
                ];
            }
        }

        unset(
            $validated['pwd_id'],
            $validated['final_confirmation'],
            $validated['password_confirmation'],
            $validated['dole_certificate'],
            $validated['bir_certificate'],
            $validated['business_permit'],
            $validated['dti_certificate'],
        );

        $user = User::create($validated);

        foreach ($employerDocuments as $document) {
            EmployerDocument::create($document + ['user_id' => $user->id]);
        }

        // Notify admin workspaces only after the account and all of its
        // submitted verification files have been stored successfully.
        app(RealtimeService::class)->broadcast(new AccountRegistered($user));

        $delivery = $this->sendVerificationEmail($user);

        return redirect()
            ->route('register.verification')
            ->with($this->verificationFlashData($user->email, $delivery));
    }

    public function resendVerificationEmail(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'max:255'],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('register.verification')
                ->withErrors($validator)
                ->withInput($request->only('email'));
        }

        $email = $validator->validated()['email'];
        $user = User::where('email', $email)->first();

        if (! $user) {
            return redirect()
                ->route('register.verification')
                ->with([
                    'verification_email' => $email,
                    'verification_sent' => false,
                    'verification_status' => 'missing',
                    'verification_message' => 'No account was found for that email address. Please register first.',
                ]);
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()
                ->route('login')
                ->with('status', 'Your email is already verified. You can log in now.');
        }

        $delivery = $this->sendVerificationEmail($user);

        return redirect()
            ->route('register.verification')
            ->with($this->verificationFlashData($user->email, $delivery));
    }

    /**
     * @return array{sent: bool, status: string, message: string}
     */
    private function sendVerificationEmail(User $user): array
    {
        $configurationIssue = $this->mailConfigurationIssue();

        if ($configurationIssue) {
            return [
                'sent' => false,
                'status' => 'configuration',
                'message' => $configurationIssue,
            ];
        }

        try {
            $user->sendEmailVerificationNotification();

            return [
                'sent' => true,
                'status' => 'sent',
                'message' => 'We sent a fresh verification link to your email address.',
            ];
        } catch (TransportExceptionInterface $exception) {
            report($exception);

            return [
                'sent' => false,
                'status' => 'failed',
                'message' => $this->friendlyMailFailureMessage($exception),
            ];
        } catch (Throwable $exception) {
            report($exception);

            return [
                'sent' => false,
                'status' => 'failed',
                'message' => 'The verification email could not be sent. Please check the mail settings and try again.',
            ];
        }
    }

    /**
     * @param  array{sent: bool, status: string, message: string}  $delivery
     * @return array<string, mixed>
     */
    private function verificationFlashData(string $email, array $delivery): array
    {
        return [
            'verification_email' => $email,
            'verification_sent' => $delivery['sent'],
            'verification_status' => $delivery['status'],
            'verification_message' => $delivery['message'],
        ];
    }

    private function mailConfigurationIssue(): ?string
    {
        if (config('mail.default') !== 'smtp') {
            return null;
        }

        $host = trim((string) config('mail.mailers.smtp.host'));
        $username = trim((string) config('mail.mailers.smtp.username'));
        $password = trim((string) config('mail.mailers.smtp.password'));
        $fromAddress = trim((string) config('mail.from.address'));

        if (blank($host) || blank($username) || blank($password) || blank($fromAddress)) {
            return 'Email sending is missing one or more SMTP settings. Please update MAIL_HOST, MAIL_USERNAME, MAIL_PASSWORD, and MAIL_FROM_ADDRESS.';
        }

        if (
            str_contains($username, 'your-gmail-address') ||
            str_contains($password, 'your-gmail-app-password') ||
            str_contains($fromAddress, 'your-gmail-address')
        ) {
            return 'Email sending still uses placeholder Gmail settings. Please add a real Gmail address and Gmail App Password.';
        }

        if (str_contains(strtolower($host), 'gmail') && ! $this->looksLikeGmailAppPassword($password)) {
            return 'Gmail requires a 16-character App Password in MAIL_PASSWORD. The saved mail password is not a valid Gmail App Password.';
        }

        return null;
    }

    private function looksLikeGmailAppPassword(string $password): bool
    {
        $normalizedPassword = $this->normalizeGmailAppPassword($password);

        return (bool) preg_match('/^[A-Za-z0-9]{16}$/', $normalizedPassword);
    }

    private function normalizeGmailAppPassword(string $password): string
    {
        return preg_replace('/\s+/', '', trim($password, " \t\n\r\0\x0B\"'")) ?? '';
    }

    private function friendlyMailFailureMessage(TransportExceptionInterface $exception): string
    {
        $message = $exception->getMessage();

        if (
            str_contains($message, '535') ||
            str_contains($message, 'BadCredentials') ||
            str_contains($message, 'Username and Password not accepted')
        ) {
            return 'Gmail rejected the sender account. Generate a Gmail App Password, place it in MAIL_PASSWORD, then resend the link.';
        }

        return 'The verification email could not be sent. Please check the SMTP host, port, encryption, and internet connection.';
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
