@extends('layout.employer')

@php
    $initials = collect(explode(' ', $employer->company_name ?: $employer->name))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('');
    $profilePhotoUrl = $employer->profile_photo_url;
@endphp

@section('content')
<section class="applicant-profile-page" data-employer-profile aria-labelledby="employer-profile-title">
    <div class="applicant-profile-page__intro">
        <div><p class="applicant-profile-page__eyebrow">Employer account</p><h1 id="employer-profile-title">Profile settings</h1><p>Keep your business contact information current for applicants and platform support.</p></div>
        <span class="applicant-profile-page__sync"><i aria-hidden="true"></i> Business profile</span>
    </div>

    <div class="applicant-profile-layout">
        <aside class="applicant-profile-summary">
            <span class="applicant-profile-summary__avatar {{ $profilePhotoUrl ? 'has-image' : '' }}" data-employer-profile-avatar>
                @if ($profilePhotoUrl)
                    <img src="{{ $profilePhotoUrl }}" alt="Profile photo of {{ $employer->company_name ?: $employer->name }}">
                @else
                    {{ $initials ?: 'EH' }}
                @endif
            </span>
            <label class="applicant-profile-summary__photo-action" for="employer-profile-photo-input">
                <input id="employer-profile-photo-input" form="employer-profile-form" type="file" name="profile_photo" accept="image/jpeg,image/png,image/webp" data-employer-profile-photo-input>
                <span>Change profile photo</span>
                <small>JPG, PNG, or WEBP · up to 4 MB</small>
            </label>
            <h2 data-employer-profile-name>{{ $employer->company_name ?: $employer->name }}</h2>
            <p>Inclusive employer</p>
            <span class="applicant-profile-summary__status"><i aria-hidden="true"></i> {{ $employer->employer_document_status === 'valid' ? 'Documents valid' : 'Documents need renewal' }}</span>
            <dl><div><dt>Email</dt><dd>{{ $employer->email }}</dd></div><div><dt>Account type</dt><dd>Employer</dd></div></dl>
        </aside>

        <article class="applicant-profile-card">
            <header><div><p>Business information</p><h2>Your employer account</h2></div><span>Required fields are marked *</span></header>
            @if (session('status'))<p class="mt-4 text-sm font-semibold text-[#176c3a]">{{ session('status') }}</p>@endif
            <form id="employer-profile-form" action="{{ route('employer.profile.update') }}" method="POST" enctype="multipart/form-data" data-employer-profile-form novalidate>
                @csrf @method('PATCH')
                <div class="applicant-profile-form-grid">
                    <label class="applicant-profile-form-grid__wide"><span>Business / company name *</span><input name="company_name" value="{{ old('company_name', $employer->company_name) }}" maxlength="160" required></label>
                    <label><span>Authorized representative *</span><input name="employer_contact_name" value="{{ old('employer_contact_name', $employer->employer_contact_name) }}" maxlength="120" required></label>
                    <label><span>Contact number *</span><input name="employer_contact_number" value="{{ old('employer_contact_number', $employer->employer_contact_number) }}" inputmode="numeric" pattern="\d{10}" maxlength="10" data-phone-input required><small>10 digits only</small></label>
                    <label class="applicant-profile-form-grid__wide"><span>Email address</span><input value="{{ $employer->email }}" disabled></label>
                </div>
                <footer><p data-employer-profile-status role="status">{{ $errors->first() }}</p><button type="submit" data-employer-profile-save>Save changes</button></footer>
            </form>
        </article>
    </div>

    <section class="employer-dashboard__panel employer-dashboard__panel--documents employer-profile-verification" aria-labelledby="employer-verification-title">
        <header>
            <div>
                <p>Business verification</p>
                <h2 id="employer-verification-title">Business documents</h2>
            </div>
            <span>{{ $documentStatus === 'valid' ? 'All documents current' : 'Renew expired documents' }}</span>
        </header>
        <p class="employer-profile-verification__copy">Keep these documents current to continue publishing job postings.</p>
        <div class="employer-document-list">
            @forelse ($documents as $document)
                <article class="{{ $document->status === 'valid' ? 'is-valid' : 'is-expired' }}">
                    <span>{{ $document->status === 'valid' ? '✓' : '!' }}</span>
                    <div><strong>{{ $document->document_type }}</strong><small>Expires {{ $document->expires_at->format('M j, Y') }}</small></div>
                    <b>{{ ucfirst($document->status) }}</b>
                    @if ($document->status !== 'valid')
                        <form action="{{ route('employer.documents.renew', $document) }}" method="POST" enctype="multipart/form-data" class="employer-document-renewal">
                            @csrf
                            <label><span>Replace document</span><input type="file" name="document" accept=".pdf,.doc,.docx" required></label>
                            <button type="submit">Renew</button>
                        </form>
                    @endif
                </article>
            @empty
                <p class="employer-profile-verification__empty">No business documents have been uploaded yet.</p>
            @endforelse
        </div>
    </section>
</section>
@endsection
