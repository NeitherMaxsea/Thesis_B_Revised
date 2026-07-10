@extends('layout.app')

@php
    $verificationEmail = session('verification_email', old('email'));
    $verificationSent = session('verification_sent', false);
    $verificationStatus = session('verification_status');
    $verificationMessage = session('verification_message');
    $noticeClasses = $verificationSent
        ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
        : 'border-amber-200 bg-amber-50 text-amber-800';

    if ($verificationStatus === 'failed') {
        $noticeClasses = 'border-red-100 bg-red-50 text-red-700';
    }
@endphp

@section('content')
<section class="min-h-screen bg-[#f4f8f5] pt-20 lg:pt-24">
    <div class="flex min-h-[calc(100vh-5rem)] items-center justify-center px-5 py-10">
        <div class="auth-card w-full max-w-[560px] overflow-hidden rounded-lg border border-slate-200 bg-white p-7 shadow-[0_22px_65px_rgba(15,23,42,0.12)] sm:p-9">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-lg bg-[#e8f5ee] text-[#176c3a]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 7.5v9A2.25 2.25 0 0 1 19.5 18.75h-15A2.25 2.25 0 0 1 2.25 16.5v-9m19.5 0A2.25 2.25 0 0 0 19.5 5.25h-15A2.25 2.25 0 0 0 2.25 7.5m19.5 0-8.47 5.33a2.25 2.25 0 0 1-2.56 0L2.25 7.5" />
                </svg>
            </div>

            <h1 class="mt-5 text-center text-2xl font-bold text-slate-950">Verify your email</h1>

            @if ($verificationEmail && $verificationSent)
                <p class="mt-3 text-center text-sm leading-6 text-slate-500">
                    A confirmation link was sent to
                    <span class="font-bold text-[#176c3a]">{{ $verificationEmail }}</span>.
                    Confirm your account using the link, then this platform will safely continue your setup.
                </p>
            @elseif ($verificationEmail)
                <p class="mt-3 text-center text-sm leading-6 text-slate-500">
                    Your account was saved for
                    <span class="font-bold text-[#176c3a]">{{ $verificationEmail }}</span>,
                    but the verification email was not sent yet.
                </p>
            @else
                <p class="mt-3 text-center text-sm leading-6 text-slate-500">
                    Your account was created. Please check the email address you used during registration.
                </p>
            @endif

            @if ($verificationMessage)
                <div class="mt-5 rounded-lg border px-4 py-3 text-sm font-semibold {{ $noticeClasses }}">
                    {{ $verificationMessage }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mt-5 rounded-lg border border-red-100 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('verification.send') }}" method="POST" class="mt-7">
                @csrf

                @if ($verificationEmail)
                    <input type="hidden" name="email" value="{{ $verificationEmail }}">
                @else
                    <label for="verification-email" class="text-sm font-semibold text-slate-900">Email Address</label>
                    <input id="verification-email" name="email" type="email" value="{{ old('email') }}" placeholder="you@example.com" class="auth-field mt-2" required>
                @endif

                <button type="submit" class="auth-primary-button mt-4 w-full">
                    {{ $verificationSent ? 'Resend verification link' : 'Send verification link' }}
                </button>
            </form>

            <div class="mt-3">
                <a href="{{ route('login') }}" class="auth-secondary-button w-full">Go to Login</a>
            </div>
        </div>

        @if ($verificationEmail && $verificationSent)
            <div hidden data-verification-swal data-swal-title="Confirm your account" data-swal-text="We sent a secure verification link to {{ $verificationEmail }}. Confirm using that link, then the platform will continue your account setup." data-swal-icon="success"></div>
        @endif
    </div>
</section>
@endsection
