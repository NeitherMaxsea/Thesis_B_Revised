<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageJobListingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_lists_only_published_jobs_from_valid_employers(): void
    {
        $validEmployer = User::factory()->create([
            'account_type' => 'employer',
            'company_name' => 'Accessible Work Co.',
            'employer_document_status' => 'valid',
        ]);
        $invalidEmployer = User::factory()->create([
            'account_type' => 'employer',
            'employer_document_status' => 'expired',
        ]);

        Job::create([
            'user_id' => $validEmployer->id,
            'title' => 'Accessible Customer Support',
            'location' => 'Dasmarinas, Cavite',
            'employment_type' => 'full_time',
            'vacancies' => 2,
            'description' => 'Support customers through accessible communication.',
            'status' => 'published',
        ]);
        Job::create([
            'user_id' => $validEmployer->id,
            'title' => 'Unpublished Role',
            'location' => 'Dasmarinas, Cavite',
            'employment_type' => 'part_time',
            'vacancies' => 1,
            'description' => 'This draft must not be displayed.',
            'status' => 'draft',
        ]);
        Job::create([
            'user_id' => $invalidEmployer->id,
            'title' => 'Expired Employer Role',
            'location' => 'Dasmarinas, Cavite',
            'employment_type' => 'full_time',
            'vacancies' => 1,
            'description' => 'This role must not be displayed.',
            'status' => 'published',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Accessible Customer Support')
            ->assertSee('Accessible Work Co.')
            ->assertSee('Job details')
            ->assertSee('Full job description')
            ->assertSee('Support customers through accessible communication.')
            ->assertDontSee('Unpublished Role')
            ->assertDontSee('Expired Employer Role')
            ->assertDontSee('No Job Posting Yet');
    }
}
