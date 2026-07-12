@extends('layout.admin')

@php
    use Illuminate\Support\Str;
@endphp

@section('breadcrumb')
    <i data-lucide="house"></i><i data-lucide="chevron-right"></i><span>User Management</span><i data-lucide="chevron-right"></i><strong>Applicant List</strong>
@endsection

@section('content')
<section class="admin-applicant-page" data-admin-applicant-list data-admin-live-account-type="pwd_applicant">
    @if (session('status'))
        <div class="admin-create-alert" role="status">{{ session('status') }}</div>
    @endif

    @if (session('warning'))
        <div class="admin-create-alert admin-create-alert--warning" role="alert">{{ session('warning') }}</div>
    @endif

    <p class="admin-realtime-notice" data-admin-registration-feed aria-live="polite" hidden></p>

    <div class="admin-applicant-toolbar"><button type="button"><i data-lucide="list-filter"></i>Select Applicants</button></div>

    <div class="admin-applicant-table-card">
        <div class="admin-applicant-table-scroll">
            <table class="admin-applicant-table">
                <thead><tr><th>Applicant</th><th>Contact</th><th>Disability</th><th>Age</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
                <tbody data-admin-applicant-table-body>
                    @forelse ($applicants as $applicant)
                        @php
                            $status = match ($applicant->applicant_review_status) { 'approved' => 'Approved', 'declined' => 'Rejected', default => 'Pending' };
                            $statusClass = Str::lower($status);
                            $initials = Str::of($applicant->name)->explode(' ')->filter()->map(fn ($part) => Str::substr($part, 0, 1))->take(2)->implode('');
                            $accountId = 'PWD-'.str_pad((string) $applicant->id, 6, '0', STR_PAD_LEFT);
                        @endphp
                        <tr data-admin-applicant-row data-admin-account-id="{{ $applicant->id }}" tabindex="0" aria-label="Open {{ $applicant->name }} details">
                            <td><div class="admin-applicant-identity"><span>{{ Str::upper($initials ?: 'AU') }}</span><div><strong>{{ $applicant->name }}</strong><small>{{ $applicant->email }}</small></div></div></td>
                            <td>{{ $applicant->contact_number ?: 'Not set' }}</td><td>{{ $applicant->disability ?: 'Not set' }}</td><td>{{ $applicant->age ?: 'Not set' }}</td>
                            <td><span class="admin-applicant-status admin-applicant-status--{{ $statusClass }}">{{ $status }}</span></td><td>{{ $applicant->created_at?->format('M d, Y') ?? '—' }}</td>
                            <td>
                                <div class="admin-applicant-actions" data-admin-account-actions>
                                    <button type="button" class="admin-applicant-view" data-admin-applicant-open data-name="{{ $applicant->name }}" data-email="{{ $applicant->email }}" data-contact="{{ $applicant->contact_number ?: 'Not set' }}" data-disability="{{ $applicant->disability ?: 'Not set' }}" data-age="{{ $applicant->age ?: 'Not set' }}" data-birthdate="{{ $applicant->birthdate?->format('Y-m-d') ?? '' }}" data-address="{{ $applicant->street_address ?: 'Not set' }}" data-status="{{ $status }}" data-account-id="{{ $accountId }}" data-pwd-id="{{ $applicant->pwd_id_path ? 'Uploaded' : 'Not set' }}" data-pwd-id-url="{{ $applicant->pwd_id_path ? route('admin.applicants.pwd-id', $applicant) : '' }}" data-created="{{ $applicant->created_at?->format('M d, Y · g:i A') ?? 'Not set' }}" data-initials="{{ Str::upper($initials ?: 'AU') }}" data-update-url="{{ route('admin.applicants.update', $applicant) }}" aria-label="View {{ $applicant->name }}"><i data-lucide="eye"></i></button>
                                    @if ($applicant->applicant_review_status === 'pending')
                                        <form method="POST" action="{{ route('admin.applicants.approve', $applicant) }}" data-admin-account-approve-form data-account-name="{{ $applicant->name }}">
                                            @csrf
                                            <button type="submit" class="admin-account-action admin-account-action--approve">Approve</button>
                                        </form>
                                        <button type="button" class="admin-account-action admin-account-action--reject" data-admin-account-reject data-reject-url="{{ route('admin.applicants.decline', $applicant) }}" data-account-name="{{ $applicant->name }}">Reject</button>
                                    @else
                                        <span class="admin-account-action-complete">Reviewed</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr data-admin-applicant-empty><td colspan="7" class="admin-applicant-empty">No applicant accounts found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="admin-applicant-drawer" data-admin-applicant-drawer hidden>
        <button type="button" class="admin-applicant-drawer__backdrop" data-admin-applicant-close aria-label="Close details"></button>
        <aside class="admin-applicant-drawer__panel" role="dialog" aria-modal="true" aria-labelledby="admin-applicant-drawer-name" tabindex="-1">
            <header><strong data-admin-applicant-detail="name" id="admin-applicant-drawer-name">Applicant</strong><button type="button" data-admin-applicant-close aria-label="Close details"><i data-lucide="x"></i></button></header>
            <div data-admin-applicant-view-content>
                <section class="admin-applicant-drawer__identity"><span data-admin-applicant-detail="initials">AU</span><div><strong data-admin-applicant-detail="name">Applicant</strong><small data-admin-applicant-detail="email"></small><p><b data-admin-applicant-detail="status"></b><i data-admin-applicant-detail="account-id"></i></p></div></section>
                <section class="admin-applicant-drawer__facts"><div><span>Status</span><strong data-admin-applicant-detail="status"></strong></div><div><span>Disability</span><strong data-admin-applicant-detail="disability"></strong></div><div><span>PWD ID</span><strong data-admin-applicant-detail="pwd-id"></strong><a href="#" data-admin-applicant-document target="_blank" rel="noopener" hidden>Open secure document</a></div><div><span>Created</span><strong data-admin-applicant-detail="created"></strong></div></section>
                <section class="admin-applicant-drawer__section"><h2>Applicant Profile</h2><p>Main applicant information saved on the account.</p><dl><div><dt>Name</dt><dd data-admin-applicant-detail="name"></dd></div><div><dt>Contact</dt><dd data-admin-applicant-detail="contact"></dd></div><div><dt>Disability</dt><dd data-admin-applicant-detail="disability"></dd></div><div><dt>Birth Date</dt><dd data-admin-applicant-detail="birthdate"></dd></div><div><dt>Age</dt><dd data-admin-applicant-detail="age"></dd></div><div><dt>Address</dt><dd data-admin-applicant-detail="address"></dd></div></dl></section>
                <section class="admin-applicant-drawer__section"><h2>Account Details</h2><p>Admin-facing identity and review information.</p><dl><div><dt>Email</dt><dd data-admin-applicant-detail="email"></dd></div><div><dt>Account ID</dt><dd data-admin-applicant-detail="account-id"></dd></div><div><dt>Status</dt><dd data-admin-applicant-detail="status"></dd></div><div><dt>Created</dt><dd data-admin-applicant-detail="created"></dd></div></dl></section>
            </div>
            <form id="admin-applicant-edit-form" class="admin-applicant-edit-form" data-admin-applicant-edit-form hidden novalidate>
                <div class="admin-applicant-edit-form__heading"><h2>Edit Applicant</h2><p>Save changes directly to this account.</p></div>
                <label><span>Full name</span><input name="name" required maxlength="255"></label><label><span>Email address</span><input name="email" type="email" required maxlength="255"></label><label><span>Contact number</span><input name="contact_number" maxlength="30"></label><label><span>Disability</span><input name="disability" maxlength="160"></label><label><span>Birth date</span><input name="birthdate" type="date"></label><label><span>Age</span><input name="age" type="number" min="15" max="100"></label><label><span>Address</span><input name="street_address" maxlength="255"></label>
                <p class="admin-applicant-edit-form__error" data-admin-applicant-edit-error hidden></p>
            </form>
            <footer data-admin-applicant-view-actions><button type="button" data-admin-applicant-close>Done</button><button type="button" data-admin-applicant-edit-open>Edit Applicant</button><button type="button" class="is-danger">Disable Account</button></footer>
            <footer data-admin-applicant-edit-actions hidden><button type="button" data-admin-applicant-edit-cancel>Cancel</button><button type="submit" form="admin-applicant-edit-form" data-admin-applicant-edit-save>Save changes</button></footer>
        </aside>
    </div>
</section>
@endsection
