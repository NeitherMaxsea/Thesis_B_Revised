@extends('layout.dashboard')

@php
    $displayName = $user->first_name ?: $user->name;
    $initials = collect(explode(' ', $user->name))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('');
@endphp

@section('content')
<section class="applicant-profile-page" data-applicant-profile data-user-id="{{ $user->id }}" aria-labelledby="applicant-profile-title">
    <div class="applicant-profile-page__intro">
        <div>
            <p class="applicant-profile-page__eyebrow">Applicant account</p>
            <h1 id="applicant-profile-title">Profile settings</h1>
            <p>Keep your details current so job matches and support services can work better for you.</p>
        </div>
        <span class="applicant-profile-page__sync" data-profile-sync-status role="status"><i aria-hidden="true"></i> Live profile sync ready</span>
    </div>

    <div class="applicant-profile-layout">
        <aside class="applicant-profile-summary">
            <span class="applicant-profile-summary__avatar" data-profile-avatar>{{ $initials ?: 'PA' }}</span>
            <h2 data-profile-full-name>{{ $user->name }}</h2>
            <p data-profile-disability>{{ $user->disability ?: 'PWD Applicant' }}</p>
            <span class="applicant-profile-summary__status"><i aria-hidden="true"></i> Approved applicant</span>

            <dl>
                <div><dt>Email</dt><dd data-profile-email>{{ $user->email }}</dd></div>
                <div><dt>Location</dt><dd data-profile-location>{{ $user->street_address ? $user->street_address.', '.$user->city : 'Dasmarinas' }}</dd></div>
            </dl>
        </aside>

        <article class="applicant-profile-card">
            <header>
                <div>
                    <p>Personal information</p>
                    <h2>Your account details</h2>
                </div>
                <span>Required fields are marked *</span>
            </header>

            <form action="{{ route('applicant.profile.update') }}" method="POST" data-profile-form novalidate>
                @csrf
                @method('PATCH')
                <div class="applicant-profile-form-grid">
                    <label>
                        <span>First name *</span>
                        <input name="first_name" value="{{ old('first_name', $user->first_name) }}" maxlength="120" data-profile-first-name required>
                    </label>
                    <label>
                        <span>Last name *</span>
                        <input name="last_name" value="{{ old('last_name', $user->last_name) }}" maxlength="120" data-profile-last-name required>
                    </label>
                    <label>
                        <span>Disability *</span>
                        <select name="disability" data-profile-disability-input required>
                            <option value="">Select disability</option>
                            @foreach ($disabilities as $disability)
                                <option value="{{ $disability }}" @selected(old('disability', $user->disability) === $disability)>{{ $disability }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span>Contact number *</span>
                        <input name="contact_number" value="{{ old('contact_number', $user->contact_number) }}" inputmode="numeric" pattern="\d{10}" maxlength="10" data-phone-input required>
                        <small>10 digits only</small>
                    </label>
                    <label class="applicant-profile-form-grid__wide">
                        <span>Street address *</span>
                        <select name="street_address" data-profile-address required>
                            <option value="">Select your barangay</option>
                            @foreach ($addresses as $address)
                                <option value="{{ $address }}" @selected(old('street_address', $user->street_address) === $address)>{{ $address }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span>City</span>
                        <input value="{{ $user->city ?: 'Dasmarinas' }}" disabled>
                    </label>
                    <label>
                        <span>Email address</span>
                        <input value="{{ $user->email }}" disabled>
                    </label>
                </div>

                <footer>
                    <p data-profile-form-status role="status"></p>
                    <button type="submit" data-profile-save>Save changes</button>
                </footer>
            </form>
        </article>
    </div>
</section>
@endsection
