@extends('layout.dashboard')

{{-- APPLICANT ONLY: this page shows the status and admin note from applicant_review_status / applicant_review_notes. --}}

@php
    $user = auth()->user();
    $status = $user?->applicant_review_status ?: 'pending';
@endphp

@section('content')
<section class="review-screen">
    <article class="review-card" @if ($status === 'approved') data-review-approved-redirect="{{ route('applicant.dashboard') }}" @endif>
        @if ($status === 'approved')
            <div class="review-check" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12.5 9.25 16.75 19 7.25" />
                </svg>
            </div>
            <p class="dashboard-eyebrow">Approved</p>
            <h1>Your account has been approved</h1>
            <p>Great, {{ $user?->first_name ?: $user?->name }}. Redirecting you to your applicant dashboard.</p>
            <a href="{{ route('applicant.dashboard') }}" class="dashboard-primary-action mt-6">Continue to Dashboard</a>
        @elseif ($status === 'declined')
            <div class="review-icon review-icon--declined" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />
                </svg>
            </div>
            <p class="dashboard-eyebrow">Declined</p>
            <h1>Admin declined your application</h1>
            <p>{{ $user?->applicant_review_notes ?: 'Your PWD ID is missing, unclear, or invalid.' }}</p>
        @else
            <div class="review-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75v5.5l3.25 2M20.25 12A8.25 8.25 0 1 1 3.75 12a8.25 8.25 0 0 1 16.5 0Z" />
                </svg>
            </div>
            <p class="dashboard-eyebrow">Waiting For Review</p>
            <h1>Your account is waiting for admin approval</h1>
            <p>We received your registration and PWD ID upload. Please wait while the admin reviews your information.</p>
        @endif
    </article>
</section>
@endsection
