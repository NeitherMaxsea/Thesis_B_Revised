@extends('layout.dashboard')

{{-- APPLICANT ONLY: update the approved applicant dashboard here. Access is guarded in routes/web.php. --}}

@php
    $user = auth()->user();
    $displayName = $user?->first_name ?: $user?->name;
@endphp

@section('content')
<section class="dashboard-hero">
    <div>
        <p class="dashboard-eyebrow">PWD Applicant</p>
        <h1>Welcome, {{ $displayName }}</h1>
        <p>Manage your profile, track job matches, and keep up with application updates in one accessible workspace.</p>
    </div>
    <a href="#settings" class="dashboard-primary-action">Complete Profile</a>
</section>

<section class="dashboard-grid dashboard-grid--stats" aria-label="Applicant summary">
    <article class="dashboard-card dashboard-stat">
        <span>Profile Status</span>
        <strong>For Review</strong>
        <small>PWD ID verification is in progress.</small>
    </article>
    <article class="dashboard-card dashboard-stat">
        <span>Job Matches</span>
        <strong>8</strong>
        <small>Based on your disability support needs.</small>
    </article>
    <article class="dashboard-card dashboard-stat">
        <span>Messages</span>
        <strong>2</strong>
        <small>Employer conversations waiting.</small>
    </article>
</section>

<section class="dashboard-grid dashboard-grid--main">
    <article id="messages" class="dashboard-card">
        <div class="dashboard-card__header">
            <div>
                <span>Inbox</span>
                <h2>Messages</h2>
            </div>
            <a href="#">View All</a>
        </div>

        <div class="dashboard-list">
            <a href="#" class="dashboard-list-item">
                <span class="dashboard-avatar">HR</span>
                <span>
                    <strong>Hiring Coordinator</strong>
                    <small>Your profile has been shortlisted for review.</small>
                </span>
            </a>
            <a href="#" class="dashboard-list-item">
                <span class="dashboard-avatar">JA</span>
                <span>
                    <strong>Job Assistance Desk</strong>
                    <small>Please keep your contact number active.</small>
                </span>
            </a>
        </div>
    </article>

    <article id="notifications" class="dashboard-card">
        <div class="dashboard-card__header">
            <div>
                <span>Updates</span>
                <h2>Notifications</h2>
            </div>
            <a href="#">Mark Read</a>
        </div>

        <div class="dashboard-list dashboard-list--timeline">
            <div class="dashboard-timeline-item">
                <strong>Verification submitted</strong>
                <small>Your PWD ID upload is ready for admin review.</small>
            </div>
            <div class="dashboard-timeline-item">
                <strong>Recommended job added</strong>
                <small>Customer support role available in Dasmarinas.</small>
            </div>
            <div class="dashboard-timeline-item">
                <strong>Accessibility settings active</strong>
                <small>Your chosen accessibility preferences follow this dashboard.</small>
            </div>
        </div>
    </article>

    <article id="settings" class="dashboard-card">
        <div class="dashboard-card__header">
            <div>
                <span>Account</span>
                <h2>Settings</h2>
            </div>
        </div>

        <div class="dashboard-profile">
            <div>
                <small>Email</small>
                <strong>{{ $user?->email }}</strong>
            </div>
            <div>
                <small>Disability</small>
                <strong>{{ $user?->disability ?: 'Not set' }}</strong>
            </div>
            <div>
                <small>Location</small>
                <strong>{{ $user?->street_address ? $user->street_address.', '.$user->city : 'Dasmarinas' }}</strong>
            </div>
        </div>

        <form action="{{ route('logout') }}" method="POST" class="mt-5">
            @csrf
            <button type="submit" class="dashboard-secondary-action">Logout</button>
        </form>
    </article>
</section>
@endsection
