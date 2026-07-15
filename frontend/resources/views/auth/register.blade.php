@extends('layout.app')

@php
    $accountType = old('account_type', 'pwd_applicant');
    $dasmaAddresses = [
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

    $disabilityTypes = config('applicant.disabilities');
    $disabilityCategories = config('applicant.disability_categories');
@endphp

@section('content')
<section class="min-h-screen bg-[#f4f8f5] pt-20 lg:pt-24">
    <div class="grid min-h-[calc(100vh-5rem)] lg:grid-cols-[0.9fr_1.1fr]">
        <aside class="relative overflow-hidden bg-gradient-to-br from-[#041d16] via-[#0d5832] to-[#18a06b] px-6 py-10 text-white sm:px-10 lg:px-16 lg:py-16">
            <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-white/8 blur-sm"></div>
            <div class="absolute -bottom-28 -left-28 h-72 w-72 rounded-full bg-white/8 blur-sm"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_18%_28%,rgba(255,255,255,.14),transparent_34%)]"></div>

            <div class="relative z-10 max-w-[620px]">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.32em] text-[#dcefd7]">Hireable Proximity</p>
                    <h2 class="mt-4 max-w-[520px] text-4xl font-bold leading-[1.12] tracking-normal sm:text-5xl">
                        Find Work Built Around Ability
                    </h2>
                    <p class="mt-5 max-w-[560px] text-sm leading-7 text-white/82">
                        A focused space for PWD applicants and inclusive employers to meet, apply, and manage opportunities with confidence.
                    </p>
                </div>

                <div class="mt-10 space-y-4 text-sm font-semibold text-white/90">
                    <p class="flex items-center gap-3">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-white text-[#176c3a]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.25 7.313a1 1 0 0 1-1.42.002L3.29 9.207a1 1 0 0 1 1.42-1.408l4.04 4.09 6.54-6.593a1 1 0 0 1 1.414-.006Z" clip-rule="evenodd" />
                            </svg>
                        </span>
                        Guided applications for PWD applicants
                    </p>
                    <p class="flex items-center gap-3">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-white text-[#176c3a]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.25 7.313a1 1 0 0 1-1.42.002L3.29 9.207a1 1 0 0 1 1.42-1.408l4.04 4.09 6.54-6.593a1 1 0 0 1 1.414-.006Z" clip-rule="evenodd" />
                            </svg>
                        </span>
                        Inclusive job matching with partner employers
                    </p>
                    <p class="flex items-center gap-3">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-white text-[#176c3a]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.25 7.313a1 1 0 0 1-1.42.002L3.29 9.207a1 1 0 0 1 1.42-1.408l4.04 4.09 6.54-6.593a1 1 0 0 1 1.414-.006Z" clip-rule="evenodd" />
                            </svg>
                        </span>
                        Simple access for applicants and employers
                    </p>
                </div>
            </div>
        </aside>

        <div class="flex items-center justify-center px-5 py-10 lg:px-10">
            <div class="auth-card w-full max-w-[620px] overflow-hidden rounded-lg border border-slate-200 bg-white shadow-[0_22px_65px_rgba(15,23,42,0.12)]">
                <div class="grid grid-cols-2 border-b border-gray-100">
                    <a href="{{ route('login') }}" class="auth-tab flex h-14 items-center justify-center text-sm font-semibold">
                        Login
                    </a>
                    <a href="{{ route('register') }}" class="auth-tab is-active flex h-14 items-center justify-center text-sm font-semibold" aria-current="page">
                        Create Account
                    </a>
                </div>

                <div class="px-6 py-8 sm:px-8">
                    <form class="auth-panel" action="{{ route('register.store') }}" method="POST" enctype="multipart/form-data" data-register-form data-register-has-errors="{{ $errors->any() ? 'true' : 'false' }}" data-disability-categories='@json($disabilityCategories)'>
                        @csrf
                        <h2 class="text-2xl font-bold text-slate-950">Create your account</h2>
                        <p class="mt-2 text-sm text-slate-500" data-register-copy>Choose your account type, then complete the required setup.</p>

                        @if ($errors->any())
                            <div class="mt-5 rounded-lg border border-red-100 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <div class="auth-stepper mt-6 {{ $errors->any() ? '' : 'is-hidden' }}" data-register-stepper>
                            <button type="button" class="is-active" data-register-step-button="1">
                                <span>1</span>
                                Basic Info
                            </button>
                            <button type="button" data-register-step-button="2">
                                <span>2</span>
                                Verification
                            </button>
                            <button type="button" data-register-step-button="3">
                                <span>3</span>
                                Account Setup
                            </button>
                        </div>

                        {{-- ROLE SELECTOR: shared entry point for PWD applicants and employers. JS behavior lives in modules/auth-registration.js. --}}
                        <div class="mt-6" data-register-role-selector>
                            <p class="text-sm font-semibold text-slate-900">Account Type</p>
                            <input type="hidden" name="account_type" value="{{ $accountType }}" data-auth-role-input>

                            <div class="mt-3 grid grid-cols-2 gap-3">
                                <button type="button" class="auth-role-card {{ $errors->any() && $accountType === 'pwd_applicant' ? 'is-active' : '' }}" data-auth-role="pwd_applicant" aria-pressed="{{ $errors->any() && $accountType === 'pwd_applicant' ? 'true' : 'false' }}">
                                    <span class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-[#e8f5ee] text-[#176c3a]">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 7.5a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0" />
                                        </svg>
                                    </span>
                                    <span class="mt-2 block text-sm font-bold text-slate-950">PWD Applicant</span>
                                    <span class="mt-1 block text-[10px] font-semibold text-slate-500">Find inclusive work</span>
                                </button>

                                <button type="button" class="auth-role-card {{ $errors->any() && $accountType === 'employer' ? 'is-active' : '' }}" data-auth-role="employer" aria-pressed="{{ $errors->any() && $accountType === 'employer' ? 'true' : 'false' }}">
                                    <span class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.75 20.25h14.5M7 20V6.75A1.75 1.75 0 0 1 8.75 5h6.5A1.75 1.75 0 0 1 17 6.75V20M9.25 8.5h1.5m2.5 0h1.5m-5.5 3h1.5m2.5 0h1.5m-5.5 3h1.5m2.5 0h1.5M10 20v-2.75h4V20" />
                                        </svg>
                                    </span>
                                    <span class="mt-2 block text-sm font-bold text-slate-950">Employer</span>
                                    <span class="mt-1 block text-[10px] font-semibold text-slate-500">Post inclusive jobs</span>
                                </button>
                            </div>

                            <p class="mt-3 hidden rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-800" data-role-switch-notice>
                                You already started this registration. Clear the current fields first before changing account type.
                            </p>
                        </div>

                        <div class="auth-role-selected is-hidden" data-register-role-summary aria-live="polite">
                            <span class="auth-role-selected__copy">Registering as <strong data-register-role-summary-label>PWD Applicant</strong></span>
                            <button type="button" data-register-change-role>Change account type</button>
                            <p class="auth-role-selected__notice is-hidden" data-role-locked-notice>Clear the current form fields first before changing account type.</p>
                        </div>

                        <div class="mt-6 {{ $errors->any() ? '' : 'is-hidden' }}" data-register-step="1">
                        {{-- APPLICANT ONLY: profile details required before admin can verify the PWD ID. --}}
                        <div data-applicant-only>
                            <div class="grid gap-4 sm:grid-cols-[1fr_1fr_8rem]">
                                <div>
                                    <label for="register-first-name" class="text-sm font-semibold text-slate-900">First Name</label>
                                    <input id="register-first-name" name="first_name" type="text" value="{{ old('first_name') }}" placeholder="First name" class="auth-field mt-2" autocomplete="given-name" pattern="[A-Za-zÑñ .'\-]+" title="Use letters only." data-name-field data-step-required>
                                </div>
                                <div>
                                    <label for="register-last-name" class="text-sm font-semibold text-slate-900">Last Name</label>
                                    <input id="register-last-name" name="last_name" type="text" value="{{ old('last_name') }}" placeholder="Last name" class="auth-field mt-2" autocomplete="family-name" pattern="[A-Za-zÑñ .'\-]+" title="Use letters only." data-name-field data-step-required>
                                </div>
                                <div>
                                    <label for="register-suffix" class="text-sm font-semibold text-slate-900">Suffix</label>
                                    <select id="register-suffix" name="suffix" class="auth-field mt-2" autocomplete="honorific-suffix">
                                        <option value="">None</option>
                                        <option value="Jr." @selected(old('suffix') === 'Jr.')>Jr.</option>
                                        <option value="Sr." @selected(old('suffix') === 'Sr.')>Sr.</option>
                                        <option value="II" @selected(old('suffix') === 'II')>II</option>
                                        <option value="III" @selected(old('suffix') === 'III')>III</option>
                                        <option value="IV" @selected(old('suffix') === 'IV')>IV</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-4 grid gap-4 sm:grid-cols-3">
                                <div>
                                    <label for="register-gender" class="text-sm font-semibold text-slate-900">Gender</label>
                                    <select id="register-gender" name="gender" class="auth-field mt-2" data-step-required>
                                        <option value="">Select</option>
                                        <option value="female" @selected(old('gender') === 'female')>Female</option>
                                        <option value="male" @selected(old('gender') === 'male')>Male</option>
                                        <option value="non_binary" @selected(old('gender') === 'non_binary')>Non-binary</option>
                                        <option value="prefer_not_to_say" @selected(old('gender') === 'prefer_not_to_say')>Prefer not to say</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="register-age" class="text-sm font-semibold text-slate-900">Age</label>
                                    <input id="register-age" name="age" type="number" min="15" max="100" step="1" inputmode="numeric" value="{{ old('age') }}" placeholder="Age" class="auth-field mt-2" data-birth-age data-step-required>
                                </div>
                                <div>
                                    <label for="register-birthdate" class="text-sm font-semibold text-slate-900">Birthdate</label>
                                    <div class="auth-date-picker mt-2" data-date-picker>
                                        <input id="register-birthdate" type="text" value="" placeholder="MM/DD/YYYY" inputmode="numeric" autocomplete="bday" pattern="\d{1,2}/\d{1,2}/\d{4}" title="Use MM/DD/YYYY." class="auth-field" data-birthdate data-initial-birthdate="{{ old('birthdate') }}" data-step-required>
                                        <input id="register-birthdate-value" name="birthdate" type="hidden" value="{{ old('birthdate') }}" data-birthdate-value>
                                        <button type="button" class="auth-date-picker__toggle" data-date-picker-toggle aria-label="Choose birthdate" aria-expanded="false" aria-controls="register-birthdate-calendar">
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4.5" y="5.5" width="15" height="14" rx="2" /><path d="M8 3.75v3.5M16 3.75v3.5M4.5 9.5h15" /></svg>
                                        </button>
                                        <section id="register-birthdate-calendar" class="auth-date-picker__popover" data-date-picker-popover hidden aria-label="Birthdate calendar">
                                            <header>
                                                <button type="button" data-date-picker-previous aria-label="Previous month"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m14.5 6-6 6 6 6" /></svg></button>
                                                <strong data-date-picker-title></strong>
                                                <button type="button" data-date-picker-next aria-label="Next month"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9.5 6 6 6-6 6" /></svg></button>
                                            </header>
                                            <div class="auth-date-picker__weekdays" aria-hidden="true"><span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span></div>
                                            <div class="auth-date-picker__days" data-date-picker-days></div>
                                            <footer><button type="button" data-date-picker-clear>Clear date</button></footer>
                                        </section>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="register-disability" class="text-sm font-semibold text-slate-900">General Disability Category</label>
                                    <select id="register-disability" name="disability" class="auth-field mt-2" data-general-disability data-step-required>
                                        <option value="">Select general disability category</option>
                                        @foreach ($disabilityTypes as $disability)
                                            <option value="{{ $disability }}" @selected(old('disability') === $disability)>{{ $disability }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="register-disability-category" class="text-sm font-semibold text-slate-900">Disability Category</label>
                                    <select id="register-disability-category" name="disability_category" class="auth-field mt-2" data-disability-category data-selected-disability-category="{{ old('disability_category') }}" data-step-required disabled>
                                        <option value="">Select general category first</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-4">
                                <label for="register-address" class="text-sm font-semibold text-slate-900">Street Address</label>
                                <select id="register-address" name="street_address" class="auth-field mt-2" data-step-required>
                                    <option value="">Select Dasmarinas address</option>
                                    @foreach ($dasmaAddresses as $address)
                                        <option value="{{ $address }}" @selected(old('street_address') === $address)>{{ $address }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mt-4">
                                <div>
                                    <label for="register-city" class="text-sm font-semibold text-slate-900">City</label>
                                    <input id="register-city" type="text" value="Dasmarinas" disabled class="auth-field mt-2 bg-slate-100 text-slate-600">
                                </div>
                            </div>
                        </div>

                        <div class="is-hidden" data-employer-only>
                            <div class="rounded-lg border border-[#cce5d4] bg-[#f2fbf5] px-4 py-3 text-xs font-semibold leading-5 text-[#276640]">
                                Set up your business profile first. You will upload the required registration documents in the next step.
                            </div>
                            <div class="mt-5">
                                <label for="register-company-name" class="text-sm font-semibold text-slate-900">Business / Company Name</label>
                                <input id="register-company-name" name="company_name" type="text" value="{{ old('company_name') }}" placeholder="Registered business name" class="auth-field mt-2" maxlength="160" data-step-required>
                            </div>
                            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="register-employer-contact-name" class="text-sm font-semibold text-slate-900">Authorized Representative</label>
                                    <input id="register-employer-contact-name" name="employer_contact_name" type="text" value="{{ old('employer_contact_name') }}" placeholder="Full name" class="auth-field mt-2" maxlength="120" data-name-field data-step-required>
                                </div>
                                <div>
                                    <label for="register-employer-contact" class="text-sm font-semibold text-slate-900">Contact Number</label>
                                    <input id="register-employer-contact" name="employer_contact_number" type="tel" value="{{ old('employer_contact_number') }}" placeholder="10 digits" inputmode="numeric" pattern="\d{10}" maxlength="10" class="auth-field mt-2" data-phone-input data-step-required>
                                </div>
                            </div>
                        </div>
                        </div>

                        <div class="mt-6 is-hidden" data-register-step="2">
                            <div data-applicant-only>
                            <div>
                                <label for="register-pwd-id" class="text-sm font-semibold text-slate-900">PWD ID Verification</label>
                                <label for="register-pwd-id" class="auth-file-upload mt-2" data-file-upload>
                                    <input id="register-pwd-id" name="pwd_id" type="file" accept=".jpg,.jpeg,.png,.pdf" class="auth-file-input" data-step-required>
                                    <span class="auth-file-upload__icon" aria-hidden="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.75V5.75M8.25 9.5 12 5.75 15.75 9.5M5.75 18.25h12.5" />
                                        </svg>
                                    </span>
                                    <span class="auth-file-upload__body">
                                        <span class="auth-file-upload__title">Upload PWD ID</span>
                                        <span class="auth-file-upload__meta" data-file-name data-no-translate>No file selected</span>
                                    </span>
                                    <span class="auth-file-upload__button">Browse</span>
                                </label>
                                <p class="mt-2 text-xs font-semibold text-slate-500">Upload JPG, PNG, or PDF. Maximum file size is 5MB.</p>
                            </div>

                            <div class="mt-5">
                                <label for="register-contact" class="text-sm font-semibold text-slate-900">Contact Number</label>
                                <input id="register-contact" name="contact_number" type="tel" value="{{ old('contact_number') }}" placeholder="10 digits" inputmode="numeric" pattern="\d{10}" maxlength="10" class="auth-field mt-2" data-phone-input data-step-required>
                            </div>
                            </div>

                            <div class="is-hidden" data-employer-only>
                                <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-xs font-semibold leading-5 text-amber-800">
                                    Upload one PDF, DOC, or DOCX for each document. Each file can be up to 10MB. The system will assign and monitor the document expiry date automatically after upload.
                                </div>
                                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                                    @foreach ([
                                        'dole_certificate' => 'DOLE Certificate',
                                        'bir_certificate' => 'BIR Certificate',
                                        'business_permit' => 'Business Permit',
                                        'dti_certificate' => 'DTI Certificate',
                                    ] as $field => $label)
                                        <div class="rounded-lg border border-slate-200 p-3">
                                            <label for="register-{{ $field }}" class="text-sm font-semibold text-slate-900">{{ $label }}</label>
                                            <label for="register-{{ $field }}" class="auth-file-upload mt-2" data-file-upload>
                                                <input id="register-{{ $field }}" name="{{ $field }}" type="file" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" class="auth-file-input" data-employer-file-input data-step-required>
                                                <span class="auth-file-upload__icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.75V5.75M8.25 9.5 12 5.75 15.75 9.5M5.75 18.25h12.5" /></svg></span>
                                                <span class="auth-file-upload__body"><span class="auth-file-upload__title">Choose {{ $label }}</span><span class="auth-file-upload__meta" data-employer-file-name="{{ $field }}">No file selected</span></span>
                                                <span class="auth-file-upload__button">Browse</span>
                                            </label>
                                            <p class="mt-2 text-[11px] font-semibold text-slate-500">PDF, DOC, or DOCX · maximum 10MB · expiry set by system</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 is-hidden" data-register-step="3">
                            <div>
                                <label for="register-email" class="text-sm font-semibold text-slate-900">Email Address</label>
                                <input id="register-email" name="email" type="email" value="{{ old('email') }}" placeholder="Your email" class="auth-field mt-2" data-step-required>
                            </div>

                            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="register-password" class="text-sm font-semibold text-slate-900">Password</label>
                                    <div class="auth-password-field mt-2">
                                        <input id="register-password" name="password" type="password" minlength="8" placeholder="Create a password" class="auth-field" data-step-required>
                                        <button type="button" class="auth-password-toggle" data-password-toggle data-password-target="register-password" aria-controls="register-password" aria-label="Show password" aria-pressed="false" title="Show password">
                                            <svg data-password-icon="show" viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.4-6 9.5-6 9.5 6 9.5 6-3.4 6-9.5 6-9.5-6-9.5-6Z" /><circle cx="12" cy="12" r="2.75" /></svg>
                                            <svg data-password-icon="hide" viewBox="0 0 24 24" aria-hidden="true" hidden><path d="m3 3 18 18M10.7 6.2A10.8 10.8 0 0 1 12 6c6.1 0 9.5 6 9.5 6a17.8 17.8 0 0 1-3.1 3.8M6.1 6.1A17.7 17.7 0 0 0 2.5 12s3.4 6 9.5 6a10.7 10.7 0 0 0 4.1-.8M9.8 9.8a3.1 3.1 0 0 0 4.4 4.4" /></svg>
                                            <span class="sr-only" data-password-toggle-text>Show password</span>
                                        </button>
                                    </div>
                                </div>
                                <div>
                                    <label for="register-password-confirmation" class="text-sm font-semibold text-slate-900">Confirm Password</label>
                                    <div class="auth-password-field mt-2">
                                        <input id="register-password-confirmation" name="password_confirmation" type="password" minlength="8" placeholder="Confirm password" class="auth-field" data-step-required>
                                        <button type="button" class="auth-password-toggle" data-password-toggle data-password-target="register-password-confirmation" aria-controls="register-password-confirmation" aria-label="Show password" aria-pressed="false" title="Show password">
                                            <svg data-password-icon="show" viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.4-6 9.5-6 9.5 6 9.5 6-3.4 6-9.5 6-9.5-6-9.5-6Z" /><circle cx="12" cy="12" r="2.75" /></svg>
                                            <svg data-password-icon="hide" viewBox="0 0 24 24" aria-hidden="true" hidden><path d="m3 3 18 18M10.7 6.2A10.8 10.8 0 0 1 12 6c6.1 0 9.5 6 9.5 6a17.8 17.8 0 0 1-3.1 3.8M6.1 6.1A17.7 17.7 0 0 0 2.5 12s3.4 6 9.5 6a10.7 10.7 0 0 0 4.1-.8M9.8 9.8a3.1 3.1 0 0 0 4.4 4.4" /></svg>
                                            <span class="sr-only" data-password-toggle-text>Show password</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <label class="mt-5 flex items-start gap-3 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-600">
                                <input type="checkbox" name="final_confirmation" value="1" class="mt-0.5 h-4 w-4 rounded border-gray-300 text-[#176c3a] focus:ring-[#176c3a]" @checked(old('final_confirmation')) data-step-required>
                                <span data-final-confirmation-copy>I confirm that my information is correct and my PWD ID is valid for verification.</span>
                            </label>
                        </div>

                        <div class="mt-7 flex flex-col-reverse gap-3 sm:flex-row sm:justify-between {{ $errors->any() ? '' : 'is-hidden' }}" data-register-actions>
                            <button type="button" class="auth-secondary-button is-hidden" data-register-prev>Back</button>
                            <button type="button" class="auth-primary-button sm:ml-auto" data-register-next>Next</button>
                            <button type="submit" class="auth-primary-button is-hidden sm:ml-auto" data-register-submit>Create Account</button>
                        </div>

                        <p class="mt-6 text-center text-sm text-slate-500">
                            Already have an account?
                            <a href="{{ route('login') }}" class="font-semibold text-[#176c3a] hover:text-[#0f4e2b]">Login</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
