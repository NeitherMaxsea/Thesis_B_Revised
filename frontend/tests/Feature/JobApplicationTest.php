<?php

namespace Tests\Feature;

use App\Events\JobApplicationSubmitted;
use App\Models\Conversation;
use App\Models\EmployerDocument;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\Message;
use App\Models\User;
use App\Services\EmployerDocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
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
        Event::fake([JobApplicationSubmitted::class]);

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

        $application = JobApplication::query()
            ->whereBelongsTo($job)
            ->whereBelongsTo($applicant, 'applicant')
            ->firstOrFail();
        $conversation = Conversation::query()
            ->where('job_application_id', $application->id)
            ->firstOrFail();

        $response->assertRedirect(route('messages.index', ['conversation' => $conversation->id]));
        $this->assertDatabaseHas('job_applications', [
            'job_id' => $job->id,
            'applicant_id' => $applicant->id,
            'status' => 'applied',
        ]);
        $this->assertDatabaseHas('conversations', [
            'id' => $conversation->id,
            'job_id' => $job->id,
            'job_application_id' => $application->id,
            'first_user_id' => min($applicant->id, $employer->id),
            'second_user_id' => max($applicant->id, $employer->id),
        ]);
        $requirementsMessage = Message::query()->where('conversation_id', $conversation->id)->firstOrFail();
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $employer->id,
            'body' => 'Mga requirement para sa Customer Support Associate',
            'message_type' => Message::TYPE_REQUIREMENTS_CARD,
        ]);
        $this->assertSame('Submit your current resume.', $requirementsMessage->metadata['items'][0]['label']);
        $this->assertSame('Wait for an interview schedule in Messages.', $requirementsMessage->metadata['items'][1]['label']);

        Event::assertDispatchedTimes(JobApplicationSubmitted::class, 1);

        $this->actingAs($applicant)->post(route('jobs.apply', $job));
        $this->assertDatabaseCount('conversations', 1);
        $this->assertDatabaseCount('messages', 1);
        Event::assertDispatchedTimes(JobApplicationSubmitted::class, 1);
    }

    public function test_each_job_application_to_the_same_employer_has_its_own_job_linked_conversation(): void
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
        $firstJob = $this->createPublishedJob($employer, 'Customer Support Associate');
        $secondJob = $this->createPublishedJob($employer, 'Data Entry Associate');

        $this->actingAs($applicant)->post(route('jobs.apply', $firstJob))->assertRedirect();
        $this->actingAs($applicant)->post(route('jobs.apply', $secondJob))->assertRedirect();

        $firstApplication = JobApplication::query()
            ->where('job_id', $firstJob->id)
            ->where('applicant_id', $applicant->id)
            ->firstOrFail();
        $secondApplication = JobApplication::query()
            ->where('job_id', $secondJob->id)
            ->where('applicant_id', $applicant->id)
            ->firstOrFail();
        $firstConversation = Conversation::query()
            ->where('job_application_id', $firstApplication->id)
            ->firstOrFail();
        $secondConversation = Conversation::query()
            ->where('job_application_id', $secondApplication->id)
            ->firstOrFail();

        $this->assertFalse($firstConversation->is($secondConversation));
        $this->assertSame($firstJob->id, $firstConversation->job_id);
        $this->assertSame($secondJob->id, $secondConversation->job_id);
        $this->assertSame($firstApplication->id, $firstConversation->job_application_id);
        $this->assertSame($secondApplication->id, $secondConversation->job_application_id);
        $this->assertSame("job_application:{$firstApplication->id}", $firstConversation->context_key);
        $this->assertSame("job_application:{$secondApplication->id}", $secondConversation->context_key);
        $this->assertTrue($firstConversation->hasParticipant($applicant));
        $this->assertTrue($firstConversation->hasParticipant($employer));
        $this->assertTrue($secondConversation->hasParticipant($applicant));
        $this->assertTrue($secondConversation->hasParticipant($employer));
        $this->assertDatabaseCount('conversations', 2);
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

    private function createPublishedJob(User $employer, string $title): Job
    {
        return Job::create([
            'user_id' => $employer->id,
            'title' => $title,
            'location' => 'Dasmarinas, Cavite',
            'employment_type' => 'full_time',
            'vacancies' => 1,
            'description' => "{$title} description.",
            'application_requirements' => 'Submit your current resume.',
            'status' => 'published',
        ]);
    }
}
