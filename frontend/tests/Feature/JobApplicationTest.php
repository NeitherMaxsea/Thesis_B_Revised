<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\EmployerDocument;
use App\Models\User;
use App\Services\EmployerDocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_applicant_can_view_a_published_job_match(): void
    {
        $employer = User::factory()->create([
            'account_type' => 'employer',
            'employer_document_status' => 'valid',
            'company_name' => 'Inclusive Works Inc.',
        ]);
        $this->createValidDocuments($employer);
        $applicant = User::factory()->create([
            'account_type' => 'pwd_applicant',
            'applicant_review_status' => 'approved',
        ]);
        Job::create([
            'user_id' => $employer->id,
            'title' => 'Accessible Customer Support',
            'location' => 'Dasmarinas, Cavite',
            'employment_type' => 'full_time',
            'vacancies' => 2,
            'description' => 'Support customers through accessible communication.',
            'status' => 'published',
        ]);

        $this
            ->actingAs($applicant)
            ->get(route('applicant.dashboard'))
            ->assertOk()
            ->assertSee('Accessible Customer Support')
            ->assertSee('Apply and message employer');
    }

    public function test_applying_to_a_job_creates_a_private_applicant_employer_conversation(): void
    {
        config(['broadcasting.default' => 'null']);

        $employer = User::factory()->create([
            'account_type' => 'employer',
            'employer_document_status' => 'valid',
            'company_name' => 'Inclusive Works Inc.',
        ]);
        $this->createValidDocuments($employer);
        $applicant = User::factory()->create([
            'account_type' => 'pwd_applicant',
            'applicant_review_status' => 'approved',
        ]);
        $job = Job::create([
            'user_id' => $employer->id,
            'title' => 'Customer Support Associate',
            'location' => 'Dasmarinas, Cavite',
            'employment_type' => 'full_time',
            'vacancies' => 1,
            'description' => 'Support customers through accessible communication.',
            'application_requirements' => "1. Submit your current resume.\n2. Wait for an interview schedule in Messages.",
            'status' => 'published',
        ]);

        $response = $this
            ->actingAs($applicant)
            ->post(route('jobs.apply', $job));

        $response->assertRedirect(route('messages.index', ['conversation' => 1]));
        $this->assertDatabaseHas('job_applications', [
            'job_id' => $job->id,
            'applicant_id' => $applicant->id,
            'status' => 'applied',
        ]);
        $this->assertDatabaseHas('conversations', [
            'first_user_id' => min($applicant->id, $employer->id),
            'second_user_id' => max($applicant->id, $employer->id),
        ]);
        $this->assertDatabaseHas('messages', [
            'conversation_id' => 1,
            'sender_id' => $employer->id,
            'body' => "Application requirements for Customer Support Associate:\n1. Submit your current resume.\n2. Wait for an interview schedule in Messages.",
        ]);

        $this->actingAs($applicant)->post(route('jobs.apply', $job));
        $this->assertDatabaseCount('messages', 1);
    }

    public function test_expired_employer_jobs_are_hidden_and_cannot_receive_applications(): void
    {
        config(['broadcasting.default' => 'null']);

        $employer = User::factory()->create([
            'account_type' => 'employer',
            'employer_document_status' => 'valid',
        ]);
        $applicant = User::factory()->create([
            'account_type' => 'pwd_applicant',
            'applicant_review_status' => 'approved',
        ]);
        $this->createValidDocuments($employer, now()->subDay());
        $job = Job::create([
            'user_id' => $employer->id,
            'title' => 'Expired Employer Role',
            'location' => 'Dasmarinas, Cavite',
            'employment_type' => 'full_time',
            'vacancies' => 1,
            'description' => 'This role must not be shown after the documents expire.',
            'status' => 'published',
        ]);

        $this->artisan('employer-documents:refresh-statuses')->assertExitCode(0);

        $this->actingAs($applicant)
            ->get(route('applicant.dashboard'))
            ->assertOk()
            ->assertDontSee('Expired Employer Role');

        $this->actingAs($applicant)
            ->post(route('jobs.apply', $job))
            ->assertNotFound();

        $this->assertDatabaseMissing('job_applications', [
            'job_id' => $job->id,
            'applicant_id' => $applicant->id,
        ]);
    }

    private function createValidDocuments(User $employer, mixed $expiresAt = null): void
    {
        foreach (EmployerDocumentService::REQUIRED_DOCUMENT_TYPES as $documentType) {
            EmployerDocument::create([
                'user_id' => $employer->id,
                'document_type' => $documentType,
                'file_path' => 'employer-documents/test.pdf',
                'expires_at' => $expiresAt ?? now()->addYear(),
                'status' => 'valid',
            ]);
        }
    }
}
