@php
    $employerUser = auth()->user();
    $employerName = $employerUser?->company_name ?: $employerUser?->name;
    $employerInitials = collect(explode(' ', $employerName ?: 'Employer'))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('');
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
<body class="employer-page" data-auth-user-id="{{ $employerUser?->id }}" data-account-type="{{ $employerUser?->account_type }}" data-unread-message-count="{{ $layoutUnreadMessageCount }}" data-inbox-updates-url="{{ route('messages.inbox-updates') }}" data-inbox-message-cursor="{{ max(0, (int) ($inboxMessageCursor ?? 0)) }}">
    <div class="employer-shell">
        <aside class="employer-sidebar" aria-label="Employer navigation">
            <a href="{{ route('employer.dashboard') }}" class="employer-sidebar__brand">
                <img src="{{ asset('images/job-employment-logo.png') }}" alt="PWD Employment">
                <span>Employer Hub</span>
            </a>

            <nav>
                <a href="{{ route('employer.dashboard') }}" class="is-active">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="4" width="6" height="6" rx="1" /><rect x="14" y="4" width="6" height="6" rx="1" /><rect x="4" y="14" width="6" height="6" rx="1" /><rect x="14" y="14" width="6" height="6" rx="1" /></svg>
                    Overview
                </a>
                <a href="#job-postings">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="7" width="18" height="13" rx="2" /><path d="M8 7V5.5A1.5 1.5 0 0 1 9.5 4h5A1.5 1.5 0 0 1 16 5.5V7M3 12h18" /></svg>
                    Job Postings
                </a>
                <a href="#documents">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 3h8l4 4v14H6z" /><path d="M14 3v5h5M9 13h6M9 17h6" /></svg>
                    Documents
                </a>
            </nav>

            <form action="{{ route('logout') }}" method="POST" class="employer-sidebar__logout">
                @csrf
                <button type="submit"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 6H5v12h5M14 8l4 4-4 4M18 12H9" /></svg> Logout</button>
            </form>
        </aside>

        <div class="employer-content">
            <header class="employer-topbar">
                <div>
                    <span>Business dashboard</span>
                    <strong>{{ $employerName }}</strong>
                </div>
                <div class="employer-topbar__actions">
                    <span class="employer-topbar__chip {{ $employerUser?->employer_document_status === 'valid' ? 'is-valid' : 'is-expired' }}">
                        <i aria-hidden="true"></i>
                        {{ $employerUser?->employer_document_status === 'valid' ? 'Documents valid' : 'Action needed' }}
                    </span>
                    <div class="dashboard-notification-wrap">
                        <button type="button" class="dashboard-icon-button" data-dashboard-notification-toggle aria-expanded="false" aria-controls="employer-notifications" aria-label="Notifications" title="Notifications">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15.75 18a3.75 3.75 0 0 1-7.5 0M18.25 15.75H5.75l1.5-2.25V9.75a4.75 4.75 0 0 1 9.5 0v3.75l1.5 2.25Z" /></svg>
                            <span class="dashboard-badge" data-job-application-badge data-count="{{ $layoutJobApplicationCount }}" @if ($layoutJobApplicationCount === 0) hidden @endif>{{ $layoutJobApplicationCount > 99 ? '99+' : $layoutJobApplicationCount }}</span>
                        </button>
                        <div id="employer-notifications" class="dashboard-notification-menu" data-dashboard-notification-menu hidden>
                            <div class="dashboard-notification-menu__header">
                                <strong>Notifications</strong>
                                <span>Applications</span>
                            </div>
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
                        </div>
                    </div>
                    <a href="{{ route('messages.index') }}" class="dashboard-icon-button" data-messages-link aria-label="Messages" title="Messages">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4.75 6.75A2.25 2.25 0 0 1 7 4.5h10A2.25 2.25 0 0 1 19.25 6.75v6.5A2.25 2.25 0 0 1 17 15.5H9.25L5 19.25v-12.5Z" /></svg>
                        <span class="dashboard-badge" data-unread-message-badge @if ($layoutUnreadMessageCount === 0) hidden @endif>{{ $layoutUnreadMessageCount > 99 ? '99+' : $layoutUnreadMessageCount }}</span>
                    </a>
                    <div class="dashboard-profile-menu" data-dashboard-profile-menu>
                        <button type="button" class="dashboard-profile-menu__toggle" data-dashboard-profile-toggle aria-expanded="false" aria-controls="employer-profile-options">
                            <span class="dashboard-profile-menu__avatar" aria-hidden="true">{{ $employerInitials ?: 'EH' }}</span>
                            <span class="dashboard-profile-menu__copy"><strong>{{ $employerName }}</strong><small>Employer account</small></span>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m7 10 5 5 5-5" /></svg>
                        </button>
                        <div id="employer-profile-options" class="dashboard-profile-menu__options" data-dashboard-profile-options hidden>
                            <a href="{{ route('employer.profile') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.25" /><path d="M5.5 20a6.5 6.5 0 0 1 13 0" /></svg> Profile settings</a>
                            <form action="{{ route('logout') }}" method="POST">@csrf<button type="submit"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 6H5v12h5M14 8l4 4-4 4M18 12H9" /></svg> Logout</button></form>
                        </div>
                    </div>
                </div>
            </header>
            <main class="employer-main">
                @yield('content')
            </main>
        </div>
    </div>

    <x-accessibility />
    <x-loading />
</body>
</html>
