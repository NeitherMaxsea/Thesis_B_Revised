@extends('layout.dashboard')

@php
    $documentsValid = $documentStatus === 'valid';
@endphp

@section('content')
<section class="employer-dashboard" data-employer-dashboard>
    @if (session('status'))
        <p class="employer-dashboard__flash" role="status">{{ session('status') }}</p>
    @endif
    <div class="employer-dashboard__hero">
        <div>
            <p>Inclusive recruitment</p>
            <h1>Build an accessible team.</h1>
            <span>Publish inclusive roles and connect with qualified PWD applicants.</span>
        </div>
        <button type="button" data-employer-job-open {{ $documentsValid ? '' : 'disabled' }}>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14" /></svg>
            Post a job
        </button>
    </div>

    @unless ($documentsValid)
        <div class="employer-dashboard__alert" role="alert">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 9v4M12 17h.01M10.3 4.4 2.8 18a2 2 0 0 0 1.75 3h14.9A2 2 0 0 0 21.2 18L13.7 4.4a2 2 0 0 0-3.4 0Z" /></svg>
            <span><strong>Job posting is paused.</strong> Renew every expired business document before publishing new roles.</span>
        </div>
    @endunless

    <div class="employer-dashboard__stats">
        <article><span>Active job posts</span><strong data-employer-job-count>{{ $jobs->where('status', 'published')->count() }}</strong><small>Visible to matching applicants</small></article>
        <article><span>Applications</span><strong data-employer-application-count>{{ $jobs->sum('applications_count') }}</strong><small>New applicants can continue in secure messages</small></article>
        <article><span>Document status</span><strong>{{ $documentsValid ? 'Valid' : 'Expired' }}</strong><small>{{ $documentsValid ? 'You can publish job opportunities.' : 'Update documents to continue posting.' }}</small></article>
    </div>

    <section id="job-postings" class="employer-dashboard__panel">
        <header>
            <div><p>Recruitment</p><h2>Job postings</h2></div>
            <button type="button" data-employer-job-open {{ $documentsValid ? '' : 'disabled' }}>+ New job</button>
        </header>
        <div class="employer-jobs" data-employer-job-list>
            @forelse ($jobs as $job)
                <article class="employer-job-card">
                    <div><span class="employer-job-card__status">{{ ucfirst($job->status) }}</span><h3>{{ $job->title }}</h3><p>{{ $job->location }} · {{ str_replace('_', ' ', $job->employment_type) }}</p></div>
                    <dl><div><dt>Vacancies</dt><dd>{{ $job->vacancies }}</dd></div><div><dt>Posted</dt><dd>{{ $job->created_at->format('M j, Y') }}</dd></div></dl>
                </article>
            @empty
                <div class="employer-jobs__empty" data-employer-job-empty>
                    <span aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="13" rx="2" /><path d="M8 7V5.5A1.5 1.5 0 0 1 9.5 4h5A1.5 1.5 0 0 1 16 5.5V7M3 12h18" /></svg></span>
                    <h3>No job posts yet</h3><p>Create your first inclusive opportunity when you are ready.</p>
                </div>
            @endforelse
        </div>
    </section>

    <section id="documents" class="employer-dashboard__panel employer-dashboard__panel--documents">
        <header><div><p>Verification</p><h2>Business documents</h2></div><span>{{ $documentsValid ? 'All documents current' : 'Renew expired documents' }}</span></header>
        <div class="employer-document-list">
            @foreach ($documents as $document)
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
            @endforeach
        </div>
    </section>

    <dialog class="employer-job-modal" data-employer-job-modal aria-labelledby="employer-job-modal-title">
        <form method="dialog" class="employer-job-modal__backdrop"><button aria-label="Close job-posting dialog"></button></form>
        <div class="employer-job-modal__panel">
            <header><div><p>Create job post</p><h2 id="employer-job-modal-title">Share an inclusive opportunity</h2></div><button type="button" data-employer-job-close aria-label="Close">×</button></header>
            <form action="{{ route('employer.jobs.store') }}" method="POST" data-employer-job-form novalidate>
                @csrf
                <div class="employer-job-modal__grid">
                    <label class="is-wide"><span>Job title *</span><input name="title" maxlength="160" placeholder="e.g. Customer Support Associate" required></label>
                    <label><span>Location *</span><input name="location" maxlength="160" placeholder="Dasmarinas, Cavite" required></label>
                    <label><span>Employment type *</span><select name="employment_type" required><option value="">Select type</option><option value="full_time">Full time</option><option value="part_time">Part time</option><option value="contract">Contract</option><option value="internship">Internship</option></select></label>
                    <label><span>Vacancies *</span><input name="vacancies" type="number" min="1" max="999" value="1" required></label>
                    <label><span>Minimum salary</span><input name="salary_min" type="number" min="0" placeholder="Optional"></label>
                    <label><span>Maximum salary</span><input name="salary_max" type="number" min="0" placeholder="Optional"></label>
                    <label class="is-wide"><span>Role description *</span><textarea name="description" rows="4" maxlength="5000" placeholder="Describe the responsibilities and qualifications." required></textarea></label>
                    <label class="is-wide"><span>Accessibility accommodations</span><textarea name="accommodations" rows="3" maxlength="2000" placeholder="Share available adjustments, accessible equipment, or flexible support."></textarea></label>
                    <label class="is-wide"><span>Application requirements</span><textarea name="application_requirements" rows="3" maxlength="3000" placeholder="List the documents, interview steps, or next requirements for applicants. This will be sent automatically when they apply."></textarea></label>
                </div>
                <footer><p data-employer-job-status role="status"></p><button type="submit" data-employer-job-submit>Publish job post</button></footer>
            </form>
        </div>
    </dialog>
</section>
@endsection
