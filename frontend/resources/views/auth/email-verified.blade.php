@extends('layout.app')

@section('content')
<section class="min-h-screen bg-[#f4f8f5] pt-20 lg:pt-24">
    <div class="flex min-h-[calc(100vh-5rem)] items-center justify-center px-5 py-10">
        <div class="auth-card w-full max-w-[520px] overflow-hidden rounded-lg border border-slate-200 bg-white p-7 text-center shadow-[0_22px_65px_rgba(15,23,42,0.12)] sm:p-9" data-email-verified-redirect="{{ $redirectUrl }}">
            <div class="review-check" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12.5 9.25 16.75 19 7.25" />
                </svg>
            </div>

            <h1 class="mt-5 text-2xl font-bold text-slate-950">Account confirmed</h1>
            <p class="mt-3 text-sm leading-6 text-slate-500">
                Your email is verified. Please wait while we securely load the next step for your account.
            </p>
            <div class="mt-7 flex items-center justify-center gap-2 text-sm font-semibold text-[#176c3a]">
                <span class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-[#176c3a] border-t-transparent"></span>
                Loading your account…
            </div>
        </div>
    </div>
</section>
@endsection
