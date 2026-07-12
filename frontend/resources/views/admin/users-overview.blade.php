@extends('layout.admin')

@php
    use Illuminate\Support\Str;

    $initialRole = in_array($initialRole, ['applicant', 'business'], true) ? $initialRole : 'all';
@endphp

@section('breadcrumb')
    <i data-lucide="house"></i>
    <i data-lucide="chevron-right"></i>
    <span>User Management</span>
    <i data-lucide="chevron-right"></i>
    <strong>{{ $initialRole === 'business' ? 'Employer List' : 'User Overview' }}</strong>
@endsection

@section('content')
<section class="admin-users-page" data-admin-users-overview data-admin-live-account-type="{{ $initialRole === 'business' ? 'employer' : ($initialRole === 'applicant' ? 'pwd_applicant' : 'all') }}">
    <p class="admin-realtime-notice" data-admin-registration-feed aria-live="polite" hidden></p>
    <div class="admin-user-filter-card" aria-label="User filters">
        <label class="admin-user-filter admin-user-filter--search">
            <span>Search</span>
            <input type="search" placeholder="Search by ID, name, or email..." autocomplete="off" data-admin-user-search>
        </label>

        <label class="admin-user-filter">
            <span>Role</span>
            <select data-admin-user-role>
                <option value="all" @selected($initialRole === 'all')>All</option>
                <option value="applicant" @selected($initialRole === 'applicant')>Applicant</option>
                <option value="business" @selected($initialRole === 'business')>Business</option>
            </select>
        </label>

        <label class="admin-user-filter">
            <span>Status</span>
            <select data-admin-user-status>
                <option value="all">All</option>
                <option value="active">Active</option>
                <option value="approved">Approved</option>
                <option value="pending">Pending</option>
                <option value="rejected">Rejected</option>
            </select>
        </label>

        <div class="admin-user-filter-actions">
            <button type="button" data-admin-user-export><i data-lucide="download"></i> Export Excel</button>
            <button type="button" data-admin-user-print><i data-lucide="printer"></i> Print</button>
            <span data-admin-user-count>{{ $users->count() }} of {{ $users->count() }} users</span>
        </div>
    </div>

    <div class="admin-user-table-card">
        <div class="admin-user-table-scroll">
            <table class="admin-user-table">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">User</th>
                        <th scope="col">Role</th>
                        <th scope="col">Registration Type</th>
                        <th scope="col">Date</th>
                    </tr>
                </thead>
                <tbody data-admin-user-table-body>
                    @forelse ($users as $user)
                        @php
                            $role = $user->account_type === 'employer' ? 'Business' : 'Applicant';
                            $roleKey = Str::lower($role);
                            $status = $user->account_type === 'employer'
                                ? 'active'
                                : match ($user->applicant_review_status) {
                                    'approved' => 'approved',
                                    'declined' => 'rejected',
                                    default => 'pending',
                                };
                            $prefix = $role === 'Business' ? 'BUS' : 'PWD';
                            $accountId = $prefix.'-'.str_pad((string) $user->id, 6, '0', STR_PAD_LEFT);
                            $initials = Str::of($user->name)->explode(' ')->filter()->map(fn ($part) => Str::substr($part, 0, 1))->take(2)->implode('');
                            $searchText = Str::lower("{$accountId} {$user->name} {$user->email} {$role} {$status}");
                        @endphp
                        <tr data-admin-user-row data-admin-account-id="{{ $user->id }}" data-search="{{ $searchText }}" data-role="{{ $roleKey }}" data-status="{{ $status }}">
                            <td class="admin-user-id">{{ $accountId }}</td>
                            <td>
                                <div class="admin-user-identity">
                                    <span class="admin-user-avatar admin-user-avatar--{{ $roleKey }}">{{ Str::upper($initials ?: 'AU') }}</span>
                                    <span><strong>{{ $user->name }}</strong><small>{{ $user->email }}</small></span>
                                </div>
                            </td>
                            <td>{{ $role }}</td>
                            <td>Self Register</td>
                            <td>{{ $user->created_at?->format('M d, Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr data-admin-user-empty><td colspan="5" class="admin-user-table-empty">No user accounts found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
