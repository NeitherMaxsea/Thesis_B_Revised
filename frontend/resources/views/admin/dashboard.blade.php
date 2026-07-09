@extends('layout.admin')

@php
    use Illuminate\Support\Facades\Storage;

    $statusStyles = [
        'pending' => 'admin-status admin-status--pending',
        'approved' => 'admin-status admin-status--approved',
        'declined' => 'admin-status admin-status--declined',
    ];
@endphp

@section('content')
<section class="dashboard-hero">
    <div>
        <p class="dashboard-eyebrow">Admin</p>
        <h1>Applicant Review Center</h1>
        <p>Review registered applicants, inspect their submitted details and PWD ID files, then approve or decline their access.</p>
    </div>
</section>

@if (session('status'))
    <div class="admin-alert">
        {{ session('status') }}
    </div>
@endif

<section class="dashboard-grid dashboard-grid--stats" aria-label="Admin summary">
    <article class="dashboard-card dashboard-stat">
        <span>Applicants</span>
        <strong>{{ $stats['applicants'] }}</strong>
        <small>Total PWD applicant accounts.</small>
    </article>
    <article class="dashboard-card dashboard-stat">
        <span>Pending</span>
        <strong>{{ $stats['pending'] }}</strong>
        <small>Waiting for review.</small>
    </article>
    <article class="dashboard-card dashboard-stat">
        <span>Employers</span>
        <strong>{{ $stats['employers'] }}</strong>
        <small>Registered employer accounts.</small>
    </article>
</section>

<section class="admin-grid">
    <div class="admin-panel">
        <div class="dashboard-card__header">
            <div>
                <span>Review Queue</span>
                <h2>Applicants</h2>
            </div>
        </div>

        <div class="admin-applicant-list">
            @forelse ($applicants as $applicant)
                @php
                    $status = $applicant->applicant_review_status ?: 'pending';
                    $fileUrl = $applicant->pwd_id_path ? Storage::url($applicant->pwd_id_path) : null;
                    $fileExtension = strtolower(pathinfo($applicant->pwd_id_path ?? '', PATHINFO_EXTENSION));
                    $isImage = in_array($fileExtension, ['jpg', 'jpeg', 'png'], true);
                @endphp

                <article class="admin-applicant-card">
                    <div class="admin-applicant-card__top">
                        <div>
                            <h3>{{ $applicant->name }}</h3>
                            <p>{{ $applicant->email }}</p>
                        </div>
                        <span class="{{ $statusStyles[$status] ?? $statusStyles['pending'] }}">{{ ucfirst($status) }}</span>
                    </div>

                    <div class="admin-detail-grid">
                        <div>
                            <small>Gender</small>
                            <strong>{{ $applicant->gender ?: 'Not provided' }}</strong>
                        </div>
                        <div>
                            <small>Age</small>
                            <strong>{{ $applicant->age ?: 'Not provided' }}</strong>
                        </div>
                        <div>
                            <small>Birthdate</small>
                            <strong>{{ $applicant->birthdate?->format('M d, Y') ?: 'Not provided' }}</strong>
                        </div>
                        <div>
                            <small>Contact</small>
                            <strong>{{ $applicant->contact_number ?: 'Not provided' }}</strong>
                        </div>
                        <div class="admin-detail-grid__wide">
                            <small>Disability</small>
                            <strong>{{ $applicant->disability ?: 'Not provided' }}</strong>
                        </div>
                        <div class="admin-detail-grid__wide">
                            <small>Address</small>
                            <strong>{{ $applicant->street_address ? $applicant->street_address.', '.$applicant->city : 'Not provided' }}</strong>
                        </div>
                    </div>

                    <div class="admin-storage-box">
                        <div>
                            <small>PWD ID Storage</small>
                            <strong>{{ $applicant->pwd_id_path ?: 'No file uploaded' }}</strong>
                        </div>

                        @if ($fileUrl)
                            @if ($isImage)
                                <a href="{{ $fileUrl }}" target="_blank" rel="noopener" class="admin-file-preview">
                                    <img src="{{ $fileUrl }}" alt="PWD ID upload for {{ $applicant->name }}">
                                </a>
                            @else
                                <a href="{{ $fileUrl }}" target="_blank" rel="noopener" class="admin-file-link">Open PDF/File</a>
                            @endif
                        @endif
                    </div>

                    @if ($applicant->applicant_review_notes)
                        <p class="admin-review-note">{{ $applicant->applicant_review_notes }}</p>
                    @endif

                    <div class="admin-review-actions">
                        <form action="{{ route('admin.applicants.approve', $applicant) }}" method="POST">
                            @csrf
                            <button type="submit" class="admin-approve-button">Approve</button>
                        </form>

                        <form action="{{ route('admin.applicants.decline', $applicant) }}" method="POST" class="admin-decline-form">
                            @csrf
                            <input type="text" name="applicant_review_notes" placeholder="Reason, e.g. no valid PWD ID">
                            <button type="submit" class="admin-decline-button">Decline</button>
                        </form>
                    </div>
                </article>
            @empty
                <p class="admin-empty">No applicant accounts yet.</p>
            @endforelse
        </div>
    </div>

    <aside class="admin-panel">
        <div class="dashboard-card__header">
            <div>
                <span>Accounts</span>
                <h2>Employers</h2>
            </div>
        </div>

        <div class="admin-employer-list">
            @forelse ($employers as $employer)
                <div class="admin-employer-item">
                    <strong>{{ $employer->name }}</strong>
                    <small>{{ $employer->email }}</small>
                </div>
            @empty
                <p class="admin-empty">No employer accounts yet.</p>
            @endforelse
        </div>
    </aside>
</section>
@endsection
