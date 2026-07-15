@php
    $adminUser = auth()->user();
    $adminOnlineUsers = collect($onlineUsers ?? []);
    $adminOnlineCount = max(0, (int) ($onlineUserCount ?? $adminOnlineUsers->count()));
    $adminUnreadMessageCount = max(0, (int) ($unreadMessageCount ?? 0));
    $adminInboxCursor = max(0, (int) ($inboxMessageCursor ?? 0));
    $adminOnlineDisplayName = static function ($user): string {
        $profileName = trim(implode(' ', array_filter([$user->first_name, $user->last_name])));

        if ($user->account_type === 'employer' && filled($user->company_name)) {
            return trim((string) $user->company_name);
        }

        return $profileName !== '' ? $profileName : $user->name;
    };
    $adminOnlineInitials = static function ($user) use ($adminOnlineDisplayName): string {
        return \Illuminate\Support\Str::of($adminOnlineDisplayName($user))
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))
            ->implode('') ?: 'AU';
    };
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PWD Employment administration dashboard.">
    <meta name="theme-color" content="#f8fbfa">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <title>Dashboard | PWD Employment Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    class="admin-page admin-dashboard-page"
    data-auth-user-id="{{ $adminUser?->id }}"
    data-account-type="{{ $adminUser?->account_type }}"
    data-unread-message-count="{{ $adminUnreadMessageCount }}"
    data-activity-url="{{ route('activity') }}"
    data-inbox-updates-url="{{ route('messages.inbox-updates') }}"
    data-inbox-message-cursor="{{ $adminInboxCursor }}"
>
    <noscript>
        <style>
            .page-entering .admin-sidebar,
            .page-entering .dashboard-navbar,
            .page-entering main { opacity: 1 !important; transform: none !important; filter: none !important; }
        </style>
    </noscript>

    <div class="admin-shell admin-dashboard-shell">
        <aside class="admin-sidebar admin-dashboard-sidebar" aria-label="Admin navigation" data-admin-sidebar>
            <a href="{{ route('admin.dashboard') }}" class="admin-sidebar__brand admin-dashboard-brand" aria-label="PWD Employment admin dashboard">
                <img src="{{ asset('images/job-employment-logo.png') }}" alt="PWD Employment">
                <span class="admin-sidebar-copy">
                    <strong>PWD</strong>
                    <small>Admin Operations</small>
                </span>
            </a>

            <button type="button" class="admin-sidebar-collapse" data-admin-sidebar-toggle aria-label="Collapse sidebar" aria-expanded="true">
                <i data-lucide="panel-left-close"></i>
            </button>

            <p class="admin-sidebar__label">Workspace</p>

            <nav class="admin-dashboard-nav" aria-label="Dashboard sections">
                <a class="{{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}" href="{{ route('admin.dashboard') }}" @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif>
                    <i data-lucide="layout-dashboard"></i><span class="admin-nav-label">Dashboard</span>
                </a>

                <div class="admin-nav-cluster {{ request()->routeIs('admin.users.*', 'admin.applicants.*') ? '' : 'is-closed' }}">
                    <button type="button" class="admin-nav-group-toggle" data-admin-nav-toggle aria-expanded="{{ request()->routeIs('admin.users.*', 'admin.applicants.*') ? 'true' : 'false' }}" aria-controls="user-management-menu">
                        <i data-lucide="users-round"></i><span class="admin-nav-label">User Management</span><i data-lucide="chevron-up" class="admin-nav-chevron"></i>
                    </button>
                    <div id="user-management-menu" class="admin-dashboard-subnav">
                        <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.index') ? 'is-active' : '' }}"><i data-lucide="eye"></i><span>User Overview</span></a>
                        <a href="{{ route('admin.users.create') }}" class="{{ request()->routeIs('admin.users.create') ? 'is-active' : '' }}"><i data-lucide="user-round-plus"></i><span>Create User</span></a>
                        <a href="{{ route('admin.applicants.index') }}" class="{{ request()->routeIs('admin.applicants.index') ? 'is-active' : '' }}"><i data-lucide="list"></i><span>Applicant List</span></a>
                        <a href="{{ route('admin.users.index', ['role' => 'business']) }}"><i data-lucide="building-2"></i><span>Employer List</span></a>
                        <a href="#pending-reviews"><i data-lucide="shield-check"></i><span>RBAC</span></a>
                        <a href="#pending-reviews"><i data-lucide="history"></i><span>Delete User History</span></a>
                    </div>
                </div>

                <div class="admin-nav-cluster is-closed">
                    <button type="button" class="admin-nav-group-toggle" data-admin-nav-toggle aria-expanded="false" aria-controls="job-management-menu">
                        <i data-lucide="briefcase-business"></i><span class="admin-nav-label">Job Management</span><i data-lucide="chevron-up" class="admin-nav-chevron"></i>
                    </button>
                    <div id="job-management-menu" class="admin-dashboard-subnav">
                        <a href="#recent-approved"><i data-lucide="briefcase"></i><span>All Job Posts</span></a>
                        <a href="#recent-approved"><i data-lucide="clipboard-list"></i><span>Test Job Posts</span></a>
                    </div>
                </div>

            </nav>

            <div class="admin-sidebar__quick-access">
                <p>Quick Access</p>
                <a href="#recent-approved"><i data-lucide="file-text"></i><span>Logs</span></a>
                <button type="button" data-admin-settings-open><i data-lucide="settings-2"></i><span>Settings</span></button>
            </div>

            <div class="admin-sidebar__account">
                <span class="admin-sidebar-avatar" aria-hidden="true">
                    {{ \Illuminate\Support\Str::of(auth()->user()?->name ?? 'Admin')->substr(0, 2)->upper() }}
                </span>
                <span class="admin-sidebar-copy">
                    <strong>{{ auth()->user()?->name ?? 'Admin User' }}</strong>
                    <small>{{ auth()->user()?->email ?? 'admin@example.com' }}</small>
                </span>
            </div>
        </aside>

        <div class="admin-content admin-dashboard-content">
            <header class="dashboard-navbar admin-topbar admin-dashboard-topbar">
                <nav class="admin-breadcrumb" aria-label="Breadcrumb">
                    @hasSection('breadcrumb')
                        @yield('breadcrumb')
                    @else
                        <i data-lucide="house"></i>
                        <i data-lucide="chevron-right"></i>
                        <strong>Dashboard</strong>
                    @endif
                </nav>

                <div class="admin-topbar__actions">
                    <div
                        class="admin-online-wrap"
                        data-admin-online-presence
                        data-online-users-url="{{ route('admin.online-users') }}"
                        data-active-users-url="{{ route('admin.active-users') }}"
                    >
                        <button type="button" class="admin-online-button" data-admin-online-toggle aria-label="Show online users" aria-expanded="false">
                            <span class="admin-online-avatars" data-admin-online-avatars aria-hidden="true">
                                @foreach ($adminOnlineUsers->take(3) as $onlineUser)
                                    <span>{{ $adminOnlineInitials($onlineUser) }}</span>
                                @endforeach
                            </span>
                            <span class="admin-online-button__copy"><b data-admin-online-count>{{ $adminOnlineCount }}</b> online</span>
                            <i data-lucide="chevron-down" aria-hidden="true"></i>
                        </button>
                        <section class="admin-online-panel" data-admin-online-panel hidden aria-label="Users currently online">
                            <header><strong>Online now</strong><span data-admin-online-count-label>{{ $adminOnlineCount }} active</span></header>
                            <div data-admin-online-list>
                                @forelse ($adminOnlineUsers as $onlineUser)
                                    <a href="{{ route('admin.active-users') }}" class="admin-online-user">
                                        <span class="admin-online-user__avatar" aria-hidden="true">{{ $adminOnlineInitials($onlineUser) }}</span>
                                        <span><strong>{{ $adminOnlineDisplayName($onlineUser) }}</strong><small>{{ $onlineUser->account_type === 'employer' ? 'Employer' : 'PWD Applicant' }}</small></span>
                                        <em>Active now</em>
                                    </a>
                                @empty
                                    <p class="admin-online-empty">No contacts are active right now.</p>
                                @endforelse
                            </div>
                            <a class="admin-online-panel__footer" href="{{ route('admin.active-users') }}">View active users<i data-lucide="arrow-up-right"></i></a>
                        </section>
                    </div>
                    <a href="{{ route('messages.index') }}" class="admin-message-button" data-messages-link aria-label="Messages" title="Messages">
                        <i data-lucide="messages-square"></i>
                        <b data-unread-message-badge @if ($adminUnreadMessageCount === 0) hidden @endif>{{ $adminUnreadMessageCount > 99 ? '99+' : $adminUnreadMessageCount }}</b>
                    </a>
                    <div class="admin-notification-wrap" data-admin-notifications data-notification-url="{{ route('admin.notifications') }}">
                        <button type="button" class="admin-notification-button" data-admin-notifications-toggle aria-label="Notifications" aria-expanded="false"><i data-lucide="bell"></i><b data-admin-notification-badge hidden>0</b></button>
                        <section class="admin-notification-panel" data-admin-notification-panel hidden aria-label="Account notifications">
                            <header><strong>Notifications</strong><span data-admin-notification-live>Live</span></header>
                            <div data-admin-notification-list><p class="admin-notification-empty">No new account activity yet.</p></div>
                        </section>
                    </div>
                    <button type="button" class="admin-dashboard-user-avatar" data-admin-logout-open aria-label="Open account menu">
                        {{ \Illuminate\Support\Str::of(auth()->user()?->name ?? 'Admin')->substr(0, 2)->upper() }}
                    </button>
                    <form id="admin-logout-form" action="{{ route('logout') }}" method="POST" hidden>@csrf</form>
                </div>
            </header>

            <main class="dashboard-main admin-main admin-dashboard-main">
                @yield('content')
            </main>
        </div>
    </div>

    <div class="admin-settings-modal" data-admin-settings-modal hidden>
        <button type="button" class="admin-settings-modal__backdrop" data-admin-settings-close aria-label="Close settings"></button>
        <section class="admin-settings-dialog" role="dialog" aria-modal="true" aria-labelledby="admin-settings-title" tabindex="-1">
            <aside class="admin-settings-nav" aria-label="Settings categories">
                <button type="button" class="admin-settings-close" data-admin-settings-close aria-label="Close settings"><i data-lucide="x"></i></button>
                <nav>
                    <button type="button" class="is-active" data-admin-settings-tab="general" aria-selected="true"><i data-lucide="settings-2"></i><span>General</span></button>
                    <button type="button" data-admin-settings-tab="notifications" aria-selected="false"><i data-lucide="bell"></i><span>Notifications</span></button>
                    <button type="button" data-admin-settings-tab="personalization" aria-selected="false"><i data-lucide="palette"></i><span>Personalization</span></button>
                    <button type="button" data-admin-settings-tab="account" aria-selected="false"><i data-lucide="circle-user-round"></i><span>Account</span></button>
                </nav>
            </aside>

            <div class="admin-settings-content">
                <section data-admin-settings-panel="general">
                    <h2 id="admin-settings-title">General</h2>
                    <div class="admin-settings-row">
                        <label for="admin-setting-appearance">Appearance</label>
                        <select id="admin-setting-appearance" data-admin-setting="appearance"><option>System</option><option>Light</option><option>Dark</option></select>
                    </div>
                    <div class="admin-settings-row">
                        <label for="admin-setting-contrast">Contrast</label>
                        <select id="admin-setting-contrast" data-admin-setting="contrast"><option>Medium</option><option>Standard</option><option>High</option></select>
                    </div>
                    <div class="admin-settings-row">
                        <label for="admin-setting-accent">Accent color</label>
                        <select id="admin-setting-accent" data-admin-setting="accent"><option>Green</option><option>Purple</option><option>Blue</option></select>
                    </div>
                    <div class="admin-settings-row">
                        <label for="admin-setting-language">Language</label>
                        <select id="admin-setting-language" data-admin-setting="language"><option>Auto-detect</option><option>English</option><option>Filipino</option></select>
                    </div>
                    <label class="admin-settings-toggle-row"><span><strong>Reduce motion</strong><small>Limit interface animation where possible.</small></span><input type="checkbox" data-admin-setting="reduceMotion"><i aria-hidden="true"></i></label>
                </section>

                <section data-admin-settings-panel="notifications" hidden>
                    <h2>Notifications</h2>
                    <label class="admin-settings-toggle-row"><span><strong>Review updates</strong><small>Get notified when new accounts are awaiting review.</small></span><input type="checkbox" data-admin-setting="reviewUpdates"><i aria-hidden="true"></i></label>
                    <label class="admin-settings-toggle-row"><span><strong>System notices</strong><small>Receive important platform and maintenance updates.</small></span><input type="checkbox" data-admin-setting="systemNotices"><i aria-hidden="true"></i></label>
                    <label class="admin-settings-toggle-row"><span><strong>Email summaries</strong><small>Send a daily activity summary to your account email.</small></span><input type="checkbox" data-admin-setting="emailSummaries"><i aria-hidden="true"></i></label>
                </section>

                <section data-admin-settings-panel="personalization" hidden>
                    <h2>Personalization</h2>
                    <label class="admin-settings-toggle-row"><span><strong>Dashboard animations</strong><small>Use smooth transitions for dashboard content.</small></span><input type="checkbox" data-admin-setting="dashboardAnimations"><i aria-hidden="true"></i></label>
                    <label class="admin-settings-toggle-row"><span><strong>Compact navigation</strong><small>Keep a smaller sidebar on desktop screens.</small></span><input type="checkbox" data-admin-setting="compactNavigation"><i aria-hidden="true"></i></label>
                    <div class="admin-settings-row">
                        <label for="admin-setting-density">Information density</label>
                        <select id="admin-setting-density" data-admin-setting="density"><option>Comfortable</option><option>Compact</option><option>Spacious</option></select>
                    </div>
                </section>

                <section data-admin-settings-panel="account" hidden>
                    <h2>Account</h2>
                    <div class="admin-settings-account-card">
                        <span>{{ \Illuminate\Support\Str::of(auth()->user()?->name ?? 'Admin')->substr(0, 2)->upper() }}</span>
                        <div><strong>{{ auth()->user()?->name ?? 'Admin User' }}</strong><small>{{ auth()->user()?->email ?? 'admin@example.com' }}</small></div>
                    </div>
                    <div class="admin-settings-row"><span>Role</span><strong>Administrator</strong></div>
                    <div class="admin-settings-row"><span>Account status</span><strong class="admin-settings-status">Active</strong></div>
                    <button type="button" class="admin-settings-action">Manage account details</button>
                </section>
            </div>
        </section>
    </div>

    <x-loading />
</body>
</html>
