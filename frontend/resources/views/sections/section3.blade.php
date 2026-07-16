<section id="jobs" class="scroll-reveal scroll-mt-24 bg-white py-12 sm:py-14" data-scroll-reveal>
    <div class="mx-auto max-w-[1120px] px-5">
        <div class="text-center">
            <h2 class="text-2xl font-bold leading-tight text-slate-950 sm:text-[30px]">Featured Job Listings</h2>
            <p class="mx-auto mt-2 max-w-[660px] text-[11px] font-medium leading-5 text-slate-600 sm:text-xs">
                Explore inclusive job opportunities designed to match the skills, talents, and abilities of Persons with Disabilities.
            </p>
        </div>
    </div>

    <div class="job-search-band mt-7 px-5 py-8">
        <div class="mx-auto max-w-[1180px]">
            <form class="grid gap-2 rounded-lg sm:grid-cols-[1fr_auto_auto_auto]" data-landing-job-filters>
                <label class="job-search-field">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.75 18a7.25 7.25 0 1 1 0-14.5 7.25 7.25 0 0 1 0 14.5Z" />
                    </svg>
                    <span class="sr-only">Search jobs</span>
                    <input type="search" placeholder="Search job title, keyword, or company" class="h-full w-full bg-transparent text-sm font-medium text-slate-900 outline-none placeholder:text-slate-500" data-landing-job-search autocomplete="off">
                </label>

                <label class="job-filter-button">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 7V6a3 3 0 0 1 6 0v1M5.75 7.75h12.5A1.75 1.75 0 0 1 20 9.5v7.75A1.75 1.75 0 0 1 18.25 19H5.75A1.75 1.75 0 0 1 4 17.25V9.5a1.75 1.75 0 0 1 1.75-1.75ZM9.25 13h5.5" />
                    </svg>
                    <span class="sr-only">Filter by job type</span>
                    <select class="job-filter-select" data-landing-job-type aria-label="Filter by job type">
                        <option value="">All types</option>
                        <option value="full_time">Full time</option>
                        <option value="part_time">Part time</option>
                        <option value="contract">Contract</option>
                        <option value="internship">Internship</option>
                    </select>
                </label>

                <label class="job-filter-button">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21s6.25-5.1 6.25-11A6.25 6.25 0 0 0 5.75 10c0 5.9 6.25 11 6.25 11Zm0-8.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" />
                    </svg>
                    <span class="sr-only">Filter by location</span>
                    <select class="job-filter-select" data-landing-job-location aria-label="Filter by location">
                        <option value="">All locations</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location }}">{{ $location }}</option>
                        @endforeach
                    </select>
                </label>

                <button type="submit" class="job-search-button">Search Jobs</button>
            </form>

            <div class="mt-5 flex flex-wrap justify-center gap-3">
                <button type="button" class="job-pill is-active" data-landing-job-pill data-job-type="">All Jobs</button>
                <button type="button" class="job-pill" data-landing-job-pill data-job-type="full_time">Full time</button>
                <button type="button" class="job-pill" data-landing-job-pill data-job-type="part_time">Part Time</button>
            </div>
        </div>
    </div>

    <div class="mx-auto mt-8 max-w-[1280px] px-5">
        @if ($jobs->isNotEmpty() && $selectedJob)
            @php
                $selectedEmployerName = $selectedJob->employer->company_name ?: $selectedJob->employer->name;
                $hasSalary = $selectedJob->salary_min || $selectedJob->salary_max;
                $salaryLabel = $selectedJob->salary_min && $selectedJob->salary_max
                    ? '₱'.number_format($selectedJob->salary_min).' – ₱'.number_format($selectedJob->salary_max).' a month'
                    : ($selectedJob->salary_min
                        ? 'From ₱'.number_format($selectedJob->salary_min).' a month'
                        : 'Up to ₱'.number_format($selectedJob->salary_max).' a month');
                $canApply = auth()->check()
                    && auth()->user()->account_type === 'pwd_applicant'
                    && auth()->user()->applicant_review_status === 'approved';
                $selectedEmployerPhotoUrl = $selectedJob->employer->profile_photo_url;
                $applyUrl = $canApply
                    ? route('applicant.dashboard', ['job' => $selectedJob->id])
                    : route('login');
                $requirements = array_values(array_filter(
                    preg_split('/\r\n|\r|\n/', (string) $selectedJob->application_requirements),
                    fn ($requirement) => trim($requirement) !== ''
                ));
            @endphp

            <div class="landing-job-workspace" data-landing-job-workspace>
                <aside class="landing-job-list-panel" aria-label="Available jobs">
                    <div class="landing-job-list-panel__heading">
                        <div>
                            <strong>Available jobs</strong>
                            <p data-landing-job-result-summary>{{ $jobs->count() }} jobs shown</p>
                        </div>
                        <span data-landing-job-count>{{ $jobs->count() }}</span>
                    </div>
                    <div class="landing-job-list" data-landing-job-list>
                        @foreach ($jobs as $job)
                            @php
                                $employerName = $job->employer->company_name ?: $job->employer->name;
                                $searchText = strtolower($job->title.' '.$employerName.' '.$job->location.' '.$job->description);
                                $isSelected = $selectedJob->is($job);
                                $jobSalary = $job->salary_min && $job->salary_max
                                    ? '₱'.number_format($job->salary_min).' – ₱'.number_format($job->salary_max)
                                    : ($job->salary_min ? 'From ₱'.number_format($job->salary_min) : null);
                                $jobSalaryDetail = $job->salary_min && $job->salary_max
                                    ? '₱'.number_format($job->salary_min).' – ₱'.number_format($job->salary_max).' a month'
                                    : ($job->salary_min
                                        ? 'From ₱'.number_format($job->salary_min).' a month'
                                        : ($job->salary_max ? 'Up to ₱'.number_format($job->salary_max).' a month' : null));
                                $jobRequirements = array_values(array_filter(
                                    preg_split('/\r\n|\r|\n/', (string) $job->application_requirements),
                                    fn ($requirement) => trim($requirement) !== ''
                                ));
                                $jobApplyUrl = $canApply
                                    ? route('applicant.dashboard', ['job' => $job->id])
                                    : route('login');
                                $jobEmployerPhotoUrl = $job->employer->profile_photo_url;
                            @endphp
                            <a href="{{ route('home', ['job' => $job->id]) }}#jobs" class="landing-job-card {{ $isSelected ? 'is-selected' : '' }}" data-landing-job-card data-job-id="{{ $job->id }}" data-search-text="{{ $searchText }}" data-job-type="{{ $job->employment_type }}" data-job-location="{{ $job->location }}" data-job-title="{{ $job->title }}" data-job-employer="{{ $employerName }}" data-job-employer-photo="{{ $jobEmployerPhotoUrl }}" data-job-salary="{{ $jobSalaryDetail }}" data-job-vacancies="{{ $job->vacancies }}" data-job-description="{{ $job->description }}" data-job-accommodations="{{ $job->accommodations }}" data-job-requirements="{{ json_encode($jobRequirements, JSON_HEX_APOS | JSON_HEX_QUOT) }}" data-job-apply-url="{{ $jobApplyUrl }}" data-job-apply-label="Apply now" @if ($isSelected) aria-current="page" @endif>
                                <span class="landing-job-card__topline">
                                    <span>PWD-friendly</span>
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 4.75h10A1.25 1.25 0 0 1 18.25 6v14l-6.25-3-6.25 3V6A1.25 1.25 0 0 1 7 4.75Z" /></svg>
                                </span>
                                <strong>{{ $job->title }}</strong>
                                <span class="landing-job-card__employer">{{ $employerName }}</span>
                                <span class="landing-job-card__location">{{ $job->location }}</span>
                                <span class="landing-job-card__tags">
                                    @if ($jobSalary)<span>{{ $jobSalary }}</span>@endif
                                    <span>{{ \Illuminate\Support\Str::headline($job->employment_type) }}</span>
                                    @if ($job->accommodations)<span>Accessibility support</span>@endif
                                </span>
                            </a>
                        @endforeach
                    </div>
                </aside>

                <article class="landing-job-detail" data-landing-job-detail aria-labelledby="landing-job-detail-title">
                    <div class="landing-job-detail__banner" aria-hidden="true"></div>
                    <div class="landing-job-detail__profile" data-landing-job-detail-profile>
                        <img src="{{ $selectedEmployerPhotoUrl }}" alt="" data-landing-job-detail-profile-image @unless($selectedEmployerPhotoUrl) hidden @endunless>
                        <span data-landing-job-detail-profile-fallback @if($selectedEmployerPhotoUrl) hidden @endif>{{ strtoupper(substr($selectedEmployerName, 0, 1)) }}</span>
                    </div>
                    <header class="landing-job-detail__header">
                        <div>
                            <h3 id="landing-job-detail-title" data-landing-job-detail-title>{{ $selectedJob->title }}</h3>
                            <p class="landing-job-detail__employer" data-landing-job-detail-employer>{{ $selectedEmployerName }}</p>
                            <p class="landing-job-detail__location" data-landing-job-detail-location>{{ $selectedJob->location }}</p>
                            <p class="landing-job-detail__salary" data-landing-job-detail-salary @unless($hasSalary) hidden @endunless>{{ $salaryLabel }}</p>
                        </div>
                        <a href="{{ $applyUrl }}" class="landing-job-detail__apply" data-landing-job-detail-apply>Apply now</a>
                    </header>

                    <div class="landing-job-detail__body">
                        <section class="landing-job-detail__section">
                            <h4>Job details</h4>
                            <dl class="landing-job-detail__facts">
                                <div>
                                    <dt>Open positions</dt>
                                    <dd data-landing-job-detail-vacancies>{{ $selectedJob->vacancies }} {{ $selectedJob->vacancies === 1 ? 'vacancy' : 'vacancies' }}</dd>
                                </div>
                                <div>
                                    <dt>Job type</dt>
                                    <dd><span data-landing-job-detail-type>{{ \Illuminate\Support\Str::headline($selectedJob->employment_type) }}</span></dd>
                                </div>
                                <div>
                                    <dt>Location</dt>
                                    <dd data-landing-job-detail-fact-location>{{ $selectedJob->location }}</dd>
                                </div>
                            </dl>
                        </section>

                        <section class="landing-job-detail__section">
                            <h4>Full job description</h4>
                            <p class="landing-job-detail__copy" data-landing-job-detail-description>{{ $selectedJob->description }}</p>
                        </section>

                        <section class="landing-job-detail__section landing-job-detail__section--support" data-landing-job-detail-support @unless($selectedJob->accommodations) hidden @endunless>
                            <h4>Accessibility support</h4>
                            <p class="landing-job-detail__copy" data-landing-job-detail-accommodations>{{ $selectedJob->accommodations }}</p>
                        </section>

                        <section class="landing-job-detail__section" data-landing-job-detail-requirements-section @unless($requirements) hidden @endunless>
                            <h4>Application requirements</h4>
                            <ul class="landing-job-detail__requirements" data-landing-job-detail-requirements>
                                @foreach ($requirements as $requirement)
                                    <li>{{ trim($requirement) }}</li>
                                @endforeach
                            </ul>
                        </section>
                    </div>
                </article>
            </div>
        @else
            <div class="job-empty-state">
                <div class="job-empty-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 7V6a3 3 0 0 1 6 0v1M5.75 7.75h12.5A1.75 1.75 0 0 1 20 9.5v7.75A1.75 1.75 0 0 1 18.25 19H5.75A1.75 1.75 0 0 1 4 17.25V9.5a1.75 1.75 0 0 1 1.75-1.75Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.75 13h6.5" />
                    </svg>
                </div>
                <h3>No Job Posting Yet</h3>
                <p>Job listings will appear here once a verified employer adds a post.</p>
            </div>
        @endif

        <div class="job-empty-state" data-landing-job-no-results hidden>
            <div class="job-empty-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 7V6a3 3 0 0 1 0-14.5Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35" />
                </svg>
            </div>
            <h3>No matching jobs found</h3>
            <p>Try another keyword, job type, or location.</p>
        </div>
    </div>
</section>
