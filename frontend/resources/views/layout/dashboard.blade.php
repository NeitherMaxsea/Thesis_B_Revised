<!DOCTYPE html>
<html lang="en">
<head>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="page-entering bg-gray-100">
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
        <a href="{{ route('applicant.dashboard') }}" class="dashboard-brand" aria-label="Applicant dashboard">
            <img src="{{ asset('images/job-employment-logo.png') }}" alt="Job Employment Personal with Disabilities">
            <span>Applicant Dashboard</span>
        </a>

        <div class="dashboard-actions">
            <a href="#messages" class="dashboard-icon-button" aria-label="Messages" title="Messages">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.75 6.75A2.25 2.25 0 0 1 7 4.5h10A2.25 2.25 0 0 1 19.25 6.75v6.5A2.25 2.25 0 0 1 17 15.5H9.25L5 19.25v-12.5Z" />
                </svg>
                <span class="dashboard-badge">2</span>
            </a>

            <div class="dashboard-notification-wrap">
                <button type="button" class="dashboard-icon-button" data-dashboard-notification-toggle aria-expanded="false" aria-controls="dashboard-notifications" aria-label="Notifications" title="Notifications">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 18a3.75 3.75 0 0 1-7.5 0M18.25 15.75H5.75l1.5-2.25V9.75a4.75 4.75 0 0 1 9.5 0v3.75l1.5 2.25Z" />
                    </svg>
                    <span class="dashboard-badge">3</span>
                </button>

                <div id="dashboard-notifications" class="dashboard-notification-menu" data-dashboard-notification-menu hidden>
                    <div class="dashboard-notification-menu__header">
                        <strong>Notifications</strong>
                        <span>Today</span>
                    </div>
                    <a href="#notifications" class="dashboard-notification-item">
                        <span class="dashboard-notification-dot"></span>
                        <span>
                            <strong>Profile review pending</strong>
                            <small>Your PWD ID will be checked by the team.</small>
                        </span>
                    </a>
                    <a href="#notifications" class="dashboard-notification-item">
                        <span class="dashboard-notification-dot"></span>
                        <span>
                            <strong>New job match</strong>
                            <small>Customer support role matches your profile.</small>
                        </span>
                    </a>
                    <a href="#notifications" class="dashboard-notification-item">
                        <span class="dashboard-notification-dot"></span>
                        <span>
                            <strong>Email verified</strong>
                            <small>Your account is ready for applicant tools.</small>
                        </span>
                    </a>
                </div>
            </div>

            <a href="#settings" class="dashboard-icon-button" aria-label="Settings" title="Settings">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.25 5.5 11.1 3.75h1.8l.85 1.75 1.9.7 1.8-.65 1.25 1.25-.65 1.8.7 1.9 1.75.85v1.8l-1.75.85-.7 1.9.65 1.8-1.25 1.25-1.8-.65-1.9.7-.85 1.75h-1.8l-.85-1.75-1.9-.7-1.8.65-1.25-1.25.65-1.8-.7-1.9-1.75-.85v-1.8l1.75-.85.7-1.9-.65-1.8 1.25-1.25 1.8.65 1.9-.7Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9.25a2.75 2.75 0 1 1 0 5.5 2.75 2.75 0 0 1 0-5.5Z" />
                </svg>
            </a>
        </div>
    </header>

    <main class="dashboard-main">
        @yield('content')
    </main>

    <x-accessibility />

    <x-loading />
</body>
</html>
