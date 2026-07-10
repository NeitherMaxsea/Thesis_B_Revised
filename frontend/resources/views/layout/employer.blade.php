@php
    $employerUser = auth()->user();
    $employerName = $employerUser?->company_name ?: $employerUser?->name;
    $employerInitials = collect(explode(' ', $employerName ?: 'Employer'))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="page-entering employer-page">
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
                    <a href="{{ route('messages.index') }}" class="dashboard-icon-button" aria-label="Messages" title="Messages">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4.75 6.75A2.25 2.25 0 0 1 7 4.5h10A2.25 2.25 0 0 1 19.25 6.75v6.5A2.25 2.25 0 0 1 17 15.5H9.25L5 19.25v-12.5Z" /></svg>
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
