@extends('layout.dashboard')

@php
    use Illuminate\Support\Facades\Storage;

    $displayName = $user->first_name ?: $user->name;
    $initials = collect(explode(' ', $user->name))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('');
    $profilePhotoUrl = $user->profile_photo_path ? Storage::disk('public')->url($user->profile_photo_path) : null;
@endphp

@section('content')
<section class="applicant-profile-page" data-applicant-profile data-user-id="{{ $user->id }}" data-disability-categories='@json($disabilityCategories)' aria-labelledby="applicant-profile-title">
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
            <span class="applicant-profile-summary__avatar {{ $profilePhotoUrl ? 'has-image' : '' }}" data-profile-avatar>
                @if ($profilePhotoUrl)
                    <img src="{{ $profilePhotoUrl }}" alt="Profile photo of {{ $user->name }}" data-profile-avatar-image>
                @else
                    {{ $initials ?: 'PA' }}
                @endif
            </span>
            <label class="applicant-profile-summary__photo-action" for="profile-photo-input">
                <input id="profile-photo-input" form="applicant-profile-form" type="file" name="profile_photo" accept="image/jpeg,image/png,image/webp" data-profile-photo-input>
                <span>Change profile photo</span>
                <small>JPG, PNG, or WebP · max 2 MB</small>
            </label>
            <h2 data-profile-full-name>{{ $user->name }}</h2>
            <p data-profile-disability>{{ $user->disability_display ?: 'PWD Applicant' }}</p>
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

            <form id="applicant-profile-form" action="{{ route('applicant.profile.update') }}" method="POST" data-profile-form novalidate>
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
                        <span>General Disability Category *</span>
                        <select name="disability" data-profile-general-disability required>
                            <option value="">Select general disability category</option>
                            @foreach ($disabilities as $disability)
                                <option value="{{ $disability }}" @selected(old('disability', $user->disability) === $disability)>{{ $disability }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span>Disability Category *</span>
                        <select name="disability_category" data-profile-disability-category required>
                            <option value="">Select disability category</option>
                            @foreach ($disabilityCategories[old('disability', $user->disability)] ?? [] as $category)
                                <option value="{{ $category }}" @selected(old('disability_category', $user->disability_category) === $category)>{{ $category }}</option>
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
