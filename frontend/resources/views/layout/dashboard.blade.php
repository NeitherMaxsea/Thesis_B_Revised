@php
    $dashboardUser = auth()->user();
    $isEmployerDashboard = $dashboardUser?->account_type === 'employer';
    $dashboardDisplayName = $isEmployerDashboard ? ($dashboardUser?->company_name ?: $dashboardUser?->name) : ($dashboardUser?->first_name ?: $dashboardUser?->name);
    $dashboardDisability = $isEmployerDashboard ? 'Employer account' : ($dashboardUser?->disability ?: 'PWD Applicant');
    $dashboardInitials = collect(explode(' ', $dashboardDisplayName ?: 'Applicant'))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('');
    $dashboardHomeRoute = $isEmployerDashboard ? route('employer.dashboard') : route('applicant.dashboard');
    $dashboardProfileRoute = $isEmployerDashboard ? route('employer.profile') : route('applicant.profile');
    $dashboardHomeLabel = $isEmployerDashboard ? 'Employer Hub' : 'Applicant Dashboard';
    $dashboardPrimaryLabel = $isEmployerDashboard ? 'Job Postings' : 'Job Matching';
    $layoutUnreadMessageCount = max(0, (int) ($unreadMessageCount ?? 0));
    $layoutJobApplications = collect($recentJobApplications ?? []);
    $layoutJobApplicationCount = $layoutJobApplications->count();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="page-entering bg-gray-100" data-auth-user-id="{{ $dashboardUser?->id }}" data-account-type="{{ $dashboardUser?->account_type }}" data-unread-message-count="{{ $layoutUnreadMessageCount }}" data-activity-url="{{ route('activity') }}">
    <noscript>
        <style>
            .page-entering .dashboard-navbar,
            .page-entering main {
                opacity: 1 !important;
                transform: none !important;
                filter: none !important;
            }
        </style>
    </noscript>

    <header class="dashboard-navbar">
        <a href="{{ $dashboardHomeRoute }}" class="dashboard-brand" aria-label="{{ $dashboardHomeLabel }}">
            <img src="{{ asset('images/job-employment-logo.png') }}" alt="Job Employment Personal with Disabilities">
            <span>{{ $dashboardHomeLabel }}</span>
        </a>

        <div class="dashboard-actions">
            <a href="{{ $dashboardHomeRoute }}{{ $isEmployerDashboard ? '#job-postings' : '' }}" class="dashboard-job-link {{ request()->routeIs($isEmployerDashboard ? 'employer.dashboard' : 'applicant.dashboard') ? 'is-active' : '' }}">
                <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="7" width="18" height="13" rx="2" /><path d="M8 7V5.5A1.5 1.5 0 0 1 9.5 4h5A1.5 1.5 0 0 1 16 5.5V7M3 12h18M10 12v2h4v-2" /></svg>
                <span>{{ $dashboardPrimaryLabel }}</span>
            </a>
            <a href="{{ route('messages.index') }}" class="dashboard-icon-button" data-messages-link aria-label="Messages" title="Messages">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.75 6.75A2.25 2.25 0 0 1 7 4.5h10A2.25 2.25 0 0 1 19.25 6.75v6.5A2.25 2.25 0 0 1 17 15.5H9.25L5 19.25v-12.5Z" />
                </svg>
                <span class="dashboard-badge" data-unread-message-badge @if ($layoutUnreadMessageCount === 0) hidden @endif>{{ $layoutUnreadMessageCount > 99 ? '99+' : $layoutUnreadMessageCount }}</span>
            </a>

            <div class="dashboard-notification-wrap">
                <button type="button" class="dashboard-icon-button" data-dashboard-notification-toggle aria-expanded="false" aria-controls="dashboard-notifications" aria-label="Notifications" title="Notifications">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 18a3.75 3.75 0 0 1-7.5 0M18.25 15.75H5.75l1.5-2.25V9.75a4.75 4.75 0 0 1 9.5 0v3.75l1.5 2.25Z" />
                    </svg>
                    @if ($isEmployerDashboard)
                        <span class="dashboard-badge" data-job-application-badge data-count="{{ $layoutJobApplicationCount }}" @if ($layoutJobApplicationCount === 0) hidden @endif>{{ $layoutJobApplicationCount > 99 ? '99+' : $layoutJobApplicationCount }}</span>
                    @else
                        <span class="dashboard-badge">3</span>
                    @endif
                </button>

                <div id="dashboard-notifications" class="dashboard-notification-menu" data-dashboard-notification-menu hidden>
                    <div class="dashboard-notification-menu__header">
                        <strong>Notifications</strong>
                        <span>{{ $isEmployerDashboard ? 'Applications' : 'Today' }}</span>
                    </div>
                    @if ($isEmployerDashboard)
                        <div data-job-application-list>
                            @forelse ($layoutJobApplications as $application)
                                @php
                                    $notificationApplicant = data_get($application, 'applicant');
                                    $notificationApplicantName = trim(implode(' ', array_filter([
                                        data_get($notificationApplicant, 'first_name'),
                                        data_get($notificationApplicant, 'last_name'),
                                    ]))) ?: data_get($notificationApplicant, 'name', 'An applicant');
                                    $notificationConversationId = data_get($application, 'conversation.id');
                                    $notificationJobTitle = data_get($application, 'job.title', 'Job posting');
                                    $notificationCreatedAt = data_get($application, 'created_at');
                                @endphp
                                <a href="{{ route('messages.index', $notificationConversationId ? ['conversation' => $notificationConversationId] : []) }}" class="dashboard-notification-item" data-job-application-id="{{ data_get($application, 'id') }}">
                                    <span class="dashboard-notification-dot" aria-hidden="true"></span>
                                    <span>
                                        <strong>{{ $notificationApplicantName }} applied</strong>
                                        <small>{{ $notificationJobTitle }}@if ($notificationCreatedAt) · {{ $notificationCreatedAt->diffForHumans() }}@endif</small>
                                    </span>
                                </a>
                            @empty
                                <p class="dashboard-notification-empty" data-job-application-empty>No new job applications.</p>
                            @endforelse
                        </div>
                    @else
                        <a href="#notifications" class="dashboard-notification-item">
                            <span class="dashboard-notification-dot"></span>
                            <span><strong>Profile review pending</strong><small>Your PWD ID will be checked by the team.</small></span>
                        </a>
                        <a href="#notifications" class="dashboard-notification-item">
                            <span class="dashboard-notification-dot"></span>
                            <span><strong>New job match</strong><small>Customer support role matches your profile.</small></span>
                        </a>
                        <a href="#notifications" class="dashboard-notification-item">
                            <span class="dashboard-notification-dot"></span>
                            <span><strong>Email verified</strong><small>Your account is ready for applicant tools.</small></span>
                        </a>
                    @endif
                </div>
            </div>

            <div class="dashboard-profile-menu" data-dashboard-profile-menu>
                <button type="button" class="dashboard-profile-menu__toggle" data-dashboard-profile-toggle aria-expanded="false" aria-controls="dashboard-profile-options">
                    <span class="dashboard-profile-menu__avatar" aria-hidden="true">{{ $dashboardInitials ?: 'PA' }}</span>
                    <span class="dashboard-profile-menu__copy">
                        <strong data-dashboard-user-name>{{ $dashboardDisplayName }}</strong>
                        <small data-dashboard-user-disability>{{ $dashboardDisability }}</small>
                    </span>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m7 10 5 5 5-5" /></svg>
                </button>

                <div id="dashboard-profile-options" class="dashboard-profile-menu__options" data-dashboard-profile-options hidden>
                    <a href="{{ $dashboardProfileRoute }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.25" /><path d="M5.5 20a6.5 6.5 0 0 1 13 0" /></svg>
                        Profile settings
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 6H5v12h5M14 8l4 4-4 4M18 12H9" /></svg>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <main class="dashboard-main">
        @yield('content')
    </main>

    <x-accessibility />

    <x-loading />
</body>
</html>
