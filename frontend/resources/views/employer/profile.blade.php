@extends('layout.employer')

@php
    $initials = collect(explode(' ', $employer->company_name ?: $employer->name))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('');
@endphp

@section('content')
<section class="applicant-profile-page" aria-labelledby="employer-profile-title">
    <div class="applicant-profile-page__intro">
        <div><p class="applicant-profile-page__eyebrow">Employer account</p><h1 id="employer-profile-title">Profile settings</h1><p>Keep your business contact information current for applicants and platform support.</p></div>
        <span class="applicant-profile-page__sync"><i aria-hidden="true"></i> Business profile</span>
    </div>

    <div class="applicant-profile-layout">
        <aside class="applicant-profile-summary">
            <span class="applicant-profile-summary__avatar">{{ $initials ?: 'EH' }}</span>
            <h2>{{ $employer->company_name ?: $employer->name }}</h2>
            <p>Inclusive employer</p>
            <span class="applicant-profile-summary__status"><i aria-hidden="true"></i> {{ $employer->employer_document_status === 'valid' ? 'Documents valid' : 'Documents need renewal' }}</span>
            <dl><div><dt>Email</dt><dd>{{ $employer->email }}</dd></div><div><dt>Account type</dt><dd>Employer</dd></div></dl>
        </aside>

        <article class="applicant-profile-card">
            <header><div><p>Business information</p><h2>Your employer account</h2></div><span>Required fields are marked *</span></header>
            @if (session('status'))<p class="mt-4 text-sm font-semibold text-[#176c3a]">{{ session('status') }}</p>@endif
            <form action="{{ route('employer.profile.update') }}" method="POST">
                @csrf @method('PATCH')
                <div class="applicant-profile-form-grid">
                    <label class="applicant-profile-form-grid__wide"><span>Business / company name *</span><input name="company_name" value="{{ old('company_name', $employer->company_name) }}" maxlength="160" required></label>
                    <label><span>Authorized representative *</span><input name="employer_contact_name" value="{{ old('employer_contact_name', $employer->employer_contact_name) }}" maxlength="120" required></label>
                    <label><span>Contact number *</span><input name="employer_contact_number" value="{{ old('employer_contact_number', $employer->employer_contact_number) }}" inputmode="numeric" pattern="\d{10}" maxlength="10" data-phone-input required><small>10 digits only</small></label>
                    <label class="applicant-profile-form-grid__wide"><span>Email address</span><input value="{{ $employer->email }}" disabled></label>
                </div>
                <footer><p>{{ $errors->first() }}</p><button type="submit">Save changes</button></footer>
            </form>
        </article>
    </div>
</section>
@endsection
