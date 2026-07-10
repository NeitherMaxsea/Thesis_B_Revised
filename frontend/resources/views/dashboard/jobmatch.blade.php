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
        <span class="job-match-page__live-status" data-job-match-live-status><i aria-hidden="true"></i> Live job updates on</span>
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
                    @php($isActive = $selectedJob?->is($job))
                    <a href="{{ route('applicant.dashboard', ['job' => $job->id]) }}" class="job-match-card {{ $isActive ? 'is-active' : '' }}" data-job-match-card data-job-match-search-text="{{ strtolower($job->title.' '.($job->employer->company_name ?: $job->employer->name).' '.$job->location) }}" @if ($isActive) aria-current="page" @endif>
                        <span class="job-match-card__logo">{{ strtoupper(substr($job->employer->company_name ?: $job->employer->name, 0, 1)) }}</span>
                        <span class="job-match-card__copy"><strong>{{ $job->title }}</strong><small>{{ $job->employer->company_name ?: $job->employer->name }}</small><em>{{ $job->location }} · {{ str_replace('_', ' ', $job->employment_type) }}</em></span>
                        <span class="job-match-card__new">New</span>
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
                <div class="job-match-detail__job">
                    <header>
                        <span class="job-match-detail__company-icon">{{ strtoupper(substr($selectedJob->employer->company_name ?: $selectedJob->employer->name, 0, 1)) }}</span>
                        <div><p>{{ $selectedJob->employer->company_name ?: $selectedJob->employer->name }}</p><h2>{{ $selectedJob->title }}</h2><span>{{ $selectedJob->location }} · {{ str_replace('_', ' ', $selectedJob->employment_type) }}</span></div>
                    </header>
                    <div class="job-match-detail__facts"><span>{{ $selectedJob->vacancies }} {{ $selectedJob->vacancies === 1 ? 'vacancy' : 'vacancies' }}</span>@if ($selectedJob->salary_min || $selectedJob->salary_max)<span>₱{{ number_format($selectedJob->salary_min ?: 0) }}–₱{{ number_format($selectedJob->salary_max ?: 0) }}</span>@endif<span>Inclusive employer</span></div>
                    <section><h3>About this role</h3><p>{{ $selectedJob->description }}</p></section>
                    @if ($selectedJob->accommodations)<section><h3>Accessibility support</h3><p>{{ $selectedJob->accommodations }}</p></section>@endif
                    @if ($selectedJob->application_requirements)<section><h3>Application requirements</h3><p class="job-match-detail__requirements">{{ $selectedJob->application_requirements }}</p></section>@endif
                    <form action="{{ route('jobs.apply', $selectedJob) }}" method="POST">@csrf<button type="submit">Apply and message employer <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m21 3-8.5 18-3.4-7.1L2 10.5 21 3Z" /><path d="m9.1 13.9 4.3-4.3" /></svg></button></form>
                </div>
            @else
                <div class="job-match-detail__empty"><span class="job-match-detail__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="13" rx="2" /><path d="M8 7V5.5A1.5 1.5 0 0 1 9.5 4h5A1.5 1.5 0 0 1 16 5.5V7M3 12h18M10 12v2h4v-2" /></svg></span><p class="job-match-detail__eyebrow">Job matching</p><h2>No job posts available yet</h2><p>We’ll show the full job details here as soon as a verified employer posts a role.</p></div>
            @endif
        </article>
    </div>
</section>
@endsection
