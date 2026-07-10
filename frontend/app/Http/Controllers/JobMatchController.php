<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobMatchController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $applicant */
        $applicant = $request->user();
        abort_unless(
            $applicant->account_type === 'pwd_applicant' && $applicant->applicant_review_status === 'approved',
            403
        );

        $jobs = Job::query()
            ->with('employer:id,name,company_name')
            ->where('status', 'published')
            ->whereHas('employer', fn ($query) => $query
                ->where('account_type', 'employer')
                ->where('employer_document_status', 'valid'))
            ->latest()
            ->get();
        $selectedJob = $request->filled('job')
            ? $jobs->firstWhere('id', $request->integer('job'))
            : $jobs->first();

        return view('dashboard.jobmatch', compact('jobs', 'selectedJob', 'applicant'));
    }
}
