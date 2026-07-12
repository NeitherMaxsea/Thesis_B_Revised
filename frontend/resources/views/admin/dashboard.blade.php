@extends('layout.admin')

@php
    use Illuminate\Support\Str;

    $profileCount = $stats['profiles'] ?? 0;
    $profileTotal = max($profileCount, 1);
    $pendingPercent = $profileCount > 0 ? round((($stats['pending'] ?? 0) / $profileTotal) * 100) : 0;
    $applicantPercent = $profileCount > 0 ? round(((($stats['applicants'] ?? 0) - ($stats['pending'] ?? 0)) / $profileTotal) * 100) : 0;
    $businessPercent = $profileCount > 0 ? max(0, 100 - $pendingPercent - $applicantPercent) : 0;
    $applicantEnd = $pendingPercent + $applicantPercent;
    $donutStyle = $profileCount > 0
        ? "background: conic-gradient(#dda936 0 {$pendingPercent}%, #377ff0 {$pendingPercent}% {$applicantEnd}%, #7839e8 {$applicantEnd}% 100%)"
        : 'background: #e7edf1';

    $metricCards = [
        ['label' => 'Applicant', 'value' => $stats['applicants'], 'note' => 'Applicant accounts', 'tone' => 'blue', 'icon' => 'users-round'],
        ['label' => 'Business', 'value' => $stats['employers'], 'note' => 'Company accounts', 'tone' => 'violet', 'icon' => 'building-2'],
        ['label' => 'Jobs', 'value' => $stats['jobs'], 'note' => 'Published listings', 'tone' => 'purple', 'icon' => 'briefcase-business'],
        ['label' => 'Deleted', 'value' => $stats['deleted'], 'note' => 'History deleted users', 'tone' => 'rose', 'icon' => 'trash-2'],
        ['label' => 'Pending', 'value' => $stats['pending'], 'note' => 'Awaiting review', 'tone' => 'amber', 'icon' => 'hourglass'],
        ['label' => 'Approved', 'value' => $stats['approved'], 'note' => 'Approved records', 'tone' => 'green', 'icon' => 'badge-check'],
        ['label' => 'Rejected', 'value' => $stats['declined'], 'note' => 'Needs follow-up', 'tone' => 'red', 'icon' => 'circle-x'],
        ['label' => 'Active', 'value' => $stats['active'], 'note' => 'Currently active', 'tone' => 'mint', 'icon' => 'circle-check'],
        ['label' => 'Inactive', 'value' => $stats['inactive'], 'note' => 'Currently inactive', 'tone' => 'slate', 'icon' => 'circle-slash'],
    ];

    $chartBottom = 180;
    $chartTop = 42;
    $chartLeft = 12;
    $chartRight = 808;
    $chartHeight = $chartBottom - $chartTop;
    $chartStep = $accountTrends->count() > 1 ? ($chartRight - $chartLeft) / ($accountTrends->count() - 1) : 0;
    $chartMaximum = max(1, (int) $accountTrends->max(fn ($point) => max($point['applicants'], $point['employers'])));
    $chartCoordinate = function (int $value, int $index) use ($chartBottom, $chartHeight, $chartMaximum, $chartLeft, $chartStep): string {
        $x = $chartLeft + ($chartStep * $index);
        $y = $chartBottom - (($value / $chartMaximum) * $chartHeight);

        return number_format($x, 1, '.', '').','.number_format($y, 1, '.', '');
    };
    $applicantChartPoints = $accountTrends->values()
        ->map(fn ($point, $index) => $chartCoordinate($point['applicants'], $index));
    $businessChartPoints = $accountTrends->values()
        ->map(fn ($point, $index) => $chartCoordinate($point['employers'], $index));
    $applicantAreaPoints = "{$chartLeft},{$chartBottom} {$applicantChartPoints->implode(' ')} {$chartRight},{$chartBottom}";
@endphp

@section('content')
<div id="admin-dashboard" class="admin-dashboard" data-admin-live-account-type="all" aria-label="Administration dashboard">
    @if (session('status'))
        <div class="admin-dashboard-alert" role="status">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="admin-dashboard-alert admin-dashboard-alert--error" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    <p class="admin-realtime-notice" data-admin-registration-feed aria-live="polite" hidden></p>

    <section class="admin-metric-grid" aria-label="Account summary">
        @foreach ($metricCards as $card)
            <article class="admin-metric-card admin-metric-card--{{ $card['tone'] }}">
                <div class="admin-metric-card__top">
                    <span>{{ $card['label'] }}</span>
                    <span class="admin-metric-card__icon" aria-hidden="true"><i data-lucide="{{ $card['icon'] }}"></i></span>
                </div>
                <strong data-admin-dashboard-metric="{{ Str::lower($card['label']) }}" @if ($card['label'] === 'Pending') data-admin-pending-count @endif>{{ $card['value'] }}</strong>
                <small>{{ $card['note'] }}</small>
            </article>
        @endforeach
    </section>

    <section class="admin-dashboard-analytics" aria-label="Account analytics">
        <article class="admin-insight-card admin-trends-card">
            <header class="admin-insight-card__heading">
                <div>
                    <h2>Account Trends</h2>
                    <p>Monthly applicant and employer activity</p>
                </div>
                <span class="admin-live-chip">6 months</span>
            </header>

            <div class="admin-trend-summaries">
                <div class="admin-trend-summary admin-trend-summary--applicant">
                    <span>Applicants</span>
                    <strong>{{ $latestApplicantCreatedAt ? 'Latest signup' : 'No activity' }}</strong>
                    <small>{{ $latestApplicantCreatedAt?->format('M d, Y · g:i A') ?? 'No applicant accounts created yet' }}</small>
                </div>
                <div class="admin-trend-summary admin-trend-summary--business">
                    <span>Employers</span>
                    <strong>{{ $stats['employers'] > 0 ? $stats['employers'] : '0' }}</strong>
                    <small>{{ $stats['employers'] }} business account{{ $stats['employers'] === 1 ? '' : 's' }}</small>
                </div>
            </div>

            <div class="admin-line-chart" role="img" aria-label="Six-month applicant and business account activity chart">
                <svg viewBox="0 0 820 230" preserveAspectRatio="none" aria-hidden="true">
                    <defs>
                        <linearGradient id="admin-area-blue" x1="0" x2="0" y1="0" y2="1">
                            <stop offset="0%" stop-color="#4e83f0" stop-opacity=".25" />
                            <stop offset="100%" stop-color="#4e83f0" stop-opacity="0" />
                        </linearGradient>
                    </defs>
                    <g class="admin-chart-grid-lines">
                        <path d="M 10 42 H 810 M 10 88 H 810 M 10 134 H 810 M 10 180 H 810" />
                    </g>
                    <polygon class="admin-chart-area" points="{{ $applicantAreaPoints }}" />
                    <polyline class="admin-chart-line admin-chart-line--applicant" points="{{ $applicantChartPoints->implode(' ') }}" />
                    <polyline class="admin-chart-line admin-chart-line--business" points="{{ $businessChartPoints->implode(' ') }}" />
                    <g class="admin-chart-dots admin-chart-dots--applicant">
                        @foreach ($applicantChartPoints as $point)
                            @php
                                [$applicantX, $applicantY] = explode(',', $point);
                            @endphp
                            <circle cx="{{ $applicantX }}" cy="{{ $applicantY }}" r="4" />
                        @endforeach
                    </g>
                    <g class="admin-chart-dots admin-chart-dots--business">
                        @foreach ($businessChartPoints as $point)
                            @php
                                [$businessX, $businessY] = explode(',', $point);
                            @endphp
                            <circle cx="{{ $businessX }}" cy="{{ $businessY }}" r="4" />
                        @endforeach
                    </g>
                </svg>
                <div class="admin-chart-legend"><span><i class="is-blue"></i>{{ $accountTrends->sum('applicants') }} Applicant</span><span><i class="is-orange"></i>{{ $accountTrends->sum('employers') }} Business</span></div>
                <div class="admin-chart-months" aria-hidden="true">
                    @foreach ($accountTrends as $trend)
                        <span>{{ $trend['label'] }}</span>
                    @endforeach
                </div>
            </div>
        </article>

        <article class="admin-insight-card admin-distribution-card">
            <header class="admin-insight-card__heading">
                <div>
                    <h2>Profile Distribution</h2>
                    <p>Pending, applicant, and employer accounts</p>
                </div>
                <span class="admin-live-chip">Live</span>
            </header>

            <div class="admin-donut-wrap">
                <div class="admin-donut" style="{{ $donutStyle }}">
                    <div>
                        <strong>{{ $stats['profiles'] }}</strong>
                        <span>Profiles synced</span>
                    </div>
                </div>
                <ul class="admin-donut-legend">
                    <li><i class="is-pending"></i>Pending: {{ $stats['pending'] }} ({{ $pendingPercent }}%)</li>
                    <li><i class="is-applicant"></i>Applicant: {{ max(0, $stats['applicants'] - $stats['pending']) }} ({{ $applicantPercent }}%)</li>
                    <li><i class="is-business"></i>Business: {{ $stats['employers'] }} ({{ $businessPercent }}%)</li>
                </ul>
            </div>
        </article>
    </section>

    <section id="recent-approved" class="admin-recent-table-card" aria-labelledby="recent-approved-title">
        <header class="admin-recent-table-card__heading">
            <div>
                <h2 id="recent-approved-title">Recent Approved</h2>
                <p>Latest approved accounts</p>
            </div>
            <span class="admin-live-chip">{{ $recentApproved->count() }} records of {{ $stats['active'] }} records</span>
        </header>

        <div class="admin-table-toolbar">
            <label class="admin-table-search">
                <i data-lucide="search" aria-hidden="true"></i>
                <input type="search" placeholder="Search approved accounts..." aria-label="Search approved accounts">
            </label>
            <button type="button" class="admin-role-filter"><i data-lucide="sliders-horizontal" aria-hidden="true"></i><span>All Roles</span><i data-lucide="chevron-down" class="admin-role-filter__chevron" aria-hidden="true"></i></button>
        </div>

        <div class="admin-table-scroll">
            <table class="admin-recent-table">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Account</th>
                        <th scope="col">Role</th>
                        <th scope="col">Created Date &amp; Time</th>
                        <th scope="col">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentApproved as $record)
                        @php
                            $account = $record['user'];
                            $prefix = $record['role'] === 'Business' ? 'BUS' : 'PWD';
                            $initials = Str::of($account->name)->explode(' ')->filter()->map(fn ($part) => Str::substr($part, 0, 1))->take(2)->implode('');
                        @endphp
                        <tr>
                            <td class="admin-record-id">{{ $prefix }}-{{ str_pad((string) $account->id, 6, '0', STR_PAD_LEFT) }}</td>
                            <td>
                                <div class="admin-account-cell">
                                    <span class="admin-account-avatar admin-account-avatar--{{ Str::lower($record['role']) }}">{{ Str::upper($initials ?: 'AU') }}</span>
                                    <span><strong>{{ $account->name }}</strong><small>{{ $account->email }}</small></span>
                                </div>
                            </td>
                            <td>{{ $record['role'] }}</td>
                            <td>{{ $account->created_at?->format('M d, Y · g:i A') ?? '—' }}</td>
                            <td><span class="admin-status-approved">Approved</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="admin-table-empty">No approved accounts yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

</div>
@endsection
