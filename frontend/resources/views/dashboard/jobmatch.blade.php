@extends('layout.dashboard')

@php
    $displayName = $applicant->first_name ?: $applicant->name;
@endphp

@section('content')
<section class="job-match-page" data-job-match aria-labelledby="job-match-heading">
    <div class="job-match-page__intro">
        <div>
            <p class="job-match-page__eyebrow">PWD Applicant</p>
            <h1 id="job-match-heading">Jobs for you</h1>
            <p>Welcome, {{ $displayName }}. Browse inclusive opportunities posted by verified employers.</p>
        </div>
        <div class="job-match-page__actions">
            <details class="job-match-page__profile-menu">
                <summary>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.25" /><path d="M5.5 20a6.5 6.5 0 0 1 13 0" /></svg>
                    Profile
                    <svg class="job-match-page__profile-menu-chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="m7 10 5 5 5-5" /></svg>
                </summary>
                <div class="job-match-page__profile-menu-options">
                    <a href="{{ route('applicant.profile') }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.25" /><path d="M5.5 20a6.5 6.5 0 0 1 13 0" /></svg>
                        View and edit profile
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 6H5v12h5M14 8l4 4-4 4M18 12H9" /></svg>
                            Logout
                        </button>
                    </form>
                </div>
            </details>
            <span class="job-match-page__live-status" data-job-match-live-status><i aria-hidden="true"></i> Live job updates on</span>
        </div>
    </div>

    <div class="job-match-workspace">
        <aside class="job-match-sidebar" aria-label="Job match list and search">
            <div class="job-match-sidebar__header">
                <h2>Job matches</h2>
                <span class="job-match-count">{{ $jobs->count() }}</span>
            </div>

            <label class="job-match-search">
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5" /><path d="m16 16 4 4" /></svg>
                <span class="sr-only">Search job matches</span>
                <input type="search" placeholder="Search a role or company..." data-job-match-search autocomplete="off">
            </label>

            <p class="job-match-update" data-job-match-update hidden><span>New job posted.</span> <button type="button" data-job-match-refresh>Refresh</button></p>

            <div class="job-match-list" data-job-match-list>
                @forelse ($jobs as $job)
                    @php
                        $jobEmployerName = $job->employer->company_name ?: $job->employer->name;
                        $isActive = $selectedJob !== null && $selectedJob->is($job);
                        $jobSummary = \Illuminate\Support\Str::limit(
                            preg_replace('/\s+/', ' ', trim($job->description)),
                            180
                        );
                    @endphp
                    <a href="{{ route('applicant.dashboard', ['job' => $job->id]) }}" class="job-match-card {{ $isActive ? 'is-active' : '' }}" data-job-match-card data-job-match-search-text="{{ strtolower($job->title.' '.$jobEmployerName.' '.$job->location.' '.$job->description) }}" aria-label="Select {{ $job->title }} at {{ $jobEmployerName }}" @if ($isActive) aria-current="page" @endif>
                        <span class="job-match-card__heading">
                            <strong>{{ $job->title }}</strong>
                        </span>
                        <span class="job-match-card__employer">{{ $jobEmployerName }}</span>
                        <span class="job-match-card__location">{{ $job->location }}</span>
                        @if ($job->accommodations)
                            <span class="job-match-card__support"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m13 2-8 12h6l-1 8 8-12h-6l1-8Z" /></svg>Accessibility support available</span>
                        @endif
                        <span class="job-match-card__summary"><i aria-hidden="true"></i>{{ $jobSummary }}</span>
                        <span class="job-match-card__footer">Posted {{ $job->created_at->diffForHumans() }}</span>
                    </a>
                @empty
                    <div class="job-match-list__empty">
                        <span class="job-match-list__empty-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="13" rx="2" /><path d="M8 7V5.5A1.5 1.5 0 0 1 9.5 4h5A1.5 1.5 0 0 1 16 5.5V7M3 12h18M10 12v2h4v-2" /></svg></span>
                        <h3>No job matches yet</h3><p>New inclusive opportunities will appear here as employers publish them.</p>
                    </div>
                @endforelse
            </div>
        </aside>

        <article class="job-match-detail" data-job-match-detail>
            @if ($selectedJob)
                @php
                    $selectedEmployerName = $selectedJob->employer->company_name ?: $selectedJob->employer->name;
                    $selectedEmployerPhotoUrl = $selectedJob->employer->profile_photo_url;
                @endphp
                <div class="job-match-detail__job">
                    <header class="job-match-detail__header">
                        <span class="job-match-detail__company-icon {{ $selectedEmployerPhotoUrl ? 'has-image' : '' }}">@if ($selectedEmployerPhotoUrl)<img src="{{ $selectedEmployerPhotoUrl }}" alt="">@else{{ strtoupper(substr($selectedEmployerName, 0, 1)) }}@endif</span>
                        <div class="job-match-detail__heading">
                            <p class="job-match-detail__eyebrow">Verified inclusive employer</p>
                            <span class="job-match-detail__company">{{ $selectedEmployerName }}</span>
                            <h2>{{ $selectedJob->title }}</h2>
                            <span class="job-match-detail__location">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 10.25c0 5.4-8 10.25-8 10.25S4 15.65 4 10.25a8 8 0 1 1 16 0Z" /><circle cx="12" cy="10.25" r="2.5" /></svg>
                                {{ $selectedJob->location }}
                                <i aria-hidden="true"></i>
                                <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="7" width="18" height="13" rx="2" /><path d="M8 7V5.5A1.5 1.5 0 0 1 9.5 4h5A1.5 1.5 0 0 1 16 5.5V7M3 12h18M10 12v2h4v-2" /></svg>
                                {{ str_replace('_', ' ', $selectedJob->employment_type) }}
                            </span>
                        </div>
                        <div class="job-match-detail__header-status">
                            <span class="job-match-detail__trust-badge">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 2.4 2.1 3.2-.3.9 3.1 2.7 1.8-1.4 2.9 1.4 2.9-2.7 1.8-.9 3.1-3.2-.3L12 21l-2.4-2.1-3.2.3-.9-3.1-2.7-1.8 1.4-2.9-1.4-2.9 2.7-1.8.9-3.1 3.2.3L12 3Z" /><path d="m8.8 12 2.05 2.05L15.4 9.5" /></svg>
                                PWD-friendly
                            </span>
                            <span class="job-match-detail__posted">Posted {{ $selectedJob->created_at->diffForHumans() }}</span>
                        </div>
                    </header>
                    <div class="job-match-detail__body">
                        @php
                            $salaryLabel = $selectedJob->salary_min && $selectedJob->salary_max
                                ? '₱'.number_format($selectedJob->salary_min).'–₱'.number_format($selectedJob->salary_max)
                                : ($selectedJob->salary_min ? 'From ₱'.number_format($selectedJob->salary_min) : 'Up to ₱'.number_format($selectedJob->salary_max));
                        @endphp
                        <dl class="job-match-detail__facts" aria-label="Job at a glance">
                            <div>
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 6v6l4 2" /><circle cx="12" cy="12" r="8.5" /></svg>
                                <span><dt>Open positions</dt><dd>{{ $selectedJob->vacancies }} {{ $selectedJob->vacancies === 1 ? 'vacancy' : 'vacancies' }}</dd></span>
                            </div>
                            @if ($selectedJob->salary_min || $selectedJob->salary_max)
                                <div>
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v18M16 7.5c0-1.1-1.8-2-4-2s-4 .9-4 2 1.8 2 4 2 4 .9 4 2-1.8 2-4 2-4 .9-4 2 1.8 2 4 2 4-.9 4-2" /></svg>
                                    <span><dt>Salary range</dt><dd>{{ $salaryLabel }}</dd></span>
                                </div>
                            @endif
                            <div>
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 2.4 2.1 3.2-.3.9 3.1 2.7 1.8-1.4 2.9 1.4 2.9-2.7 1.8-.9 3.1-3.2-.3L12 21l-2.4-2.1-3.2.3-.9-3.1-2.7-1.8 1.4-2.9-1.4-2.9 2.7-1.8.9-3.1 3.2.3L12 3Z" /><path d="m8.8 12 2.05 2.05L15.4 9.5" /></svg>
                                <span><dt>Workplace</dt><dd>Inclusive employer</dd></span>
                            </div>
                        </dl>

                        <section class="job-match-detail__section">
                            <div class="job-match-detail__section-heading"><span class="job-match-detail__section-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 4v16M4 12h16" /></svg></span><h3>About this role</h3></div>
                            <p>{{ $selectedJob->description }}</p>
                        </section>
                        @if ($selectedJob->accommodations)
                            <section class="job-match-detail__section job-match-detail__section--support">
                                <div class="job-match-detail__section-heading"><span class="job-match-detail__section-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z" /><path d="m8.5 12 2.25 2.25L15.75 9" /></svg></span><h3>Accessibility support</h3></div>
                                <p>{{ $selectedJob->accommodations }}</p>
                            </section>
                        @endif
                        @if ($selectedJob->application_requirements)
                            <section class="job-match-detail__section">
                                <div class="job-match-detail__section-heading"><span class="job-match-detail__section-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.5 3.5h8l3 3v14h-11v-17Z" /><path d="M14.5 3.5v4h4M9 12h6M9 16h4" /></svg></span><h3>Application requirements</h3></div>
                                @php
                                    $requirements = array_values(array_filter(
                                        preg_split('/\r\n|\r|\n/', $selectedJob->application_requirements),
                                        fn ($requirement) => trim($requirement) !== ''
                                    ));
                                @endphp
                                @if (count($requirements) > 1)
                                    <ul class="job-match-detail__requirements">
                                        @foreach ($requirements as $requirement)
                                            <li><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6" /></svg>{{ trim($requirement) }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="job-match-detail__requirements">{{ $selectedJob->application_requirements }}</p>
                                @endif
                            </section>
                        @endif
                    </div>

                    <form class="job-match-detail__application" action="{{ route('jobs.apply', $selectedJob) }}" method="POST" data-job-application-form>
                        @csrf
                        <div><span class="job-match-detail__application-label">Quick and private</span><strong>Interested in this role?</strong><span>Submit once, then start a secure conversation with the employer.</span></div>
                        <button type="submit" data-job-application-button>Apply and message employer <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m21 3-8.5 18-3.4-7.1L2 10.5 21 3Z" /><path d="m9.1 13.9 4.3-4.3" /></svg></button>
                        <p class="job-match-application-status" data-job-application-status role="status" aria-live="polite"></p>
                    </form>
                </div>
            @else
                <div class="job-match-detail__empty"><span class="job-match-detail__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="13" rx="2" /><path d="M8 7V5.5A1.5 1.5 0 0 1 9.5 4h5A1.5 1.5 0 0 1 16 5.5V7M3 12h18M10 12v2h4v-2" /></svg></span><p class="job-match-detail__eyebrow">Job matching</p><h2>No job posts available yet</h2><p>We’ll show the full job details here as soon as a verified employer posts a role.</p></div>
            @endif
        </article>
    </div>
</section>
@endsection
