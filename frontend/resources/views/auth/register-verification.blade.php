@extends('layout.app')

@php
    $verificationEmail = session('verification_email');
    $verificationSent = session('verification_sent', false);
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
                    We sent the verification link to
                    <span class="font-bold text-[#176c3a]">{{ $verificationEmail }}</span>.
                    Open your Gmail inbox and click the link to confirm your account.
                </p>
            @elseif ($verificationEmail)
                <p class="mt-3 text-center text-sm leading-6 text-slate-500">
                    Your account was saved for
                    <span class="font-bold text-[#176c3a]">{{ $verificationEmail }}</span>,
                    but Gmail SMTP is not configured yet.
                </p>
            @else
                <p class="mt-3 text-center text-sm leading-6 text-slate-500">
                    Your account was created. Please check the email address you used during registration.
                </p>
            @endif

            @unless ($verificationSent)
                <div class="mt-5 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800">
                    To send real Gmail verification emails, update <span class="font-bold">MAIL_USERNAME</span>,
                    <span class="font-bold">MAIL_PASSWORD</span>, and <span class="font-bold">APP_URL</span> in your .env file.
                </div>
            @endunless

            @if ($verificationSent)
                <a href="https://mail.google.com/" target="_blank" rel="noopener" class="auth-primary-button mt-7 w-full">Open Gmail</a>
            @else
                <a href="{{ route('login') }}" class="auth-primary-button mt-7 w-full">Go to Login</a>
            @endif
        </div>
    </div>
</section>
@endsection
