<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\Message;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicantChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_applicant_can_open_their_support_conversation(): void
    {
        $admin = User::factory()->create([
            'account_type' => 'admin',
            'name' => 'Platform Support',
        ]);
        $applicant = User::factory()->create([
            'account_type' => 'pwd_applicant',
            'applicant_review_status' => 'approved',
        ]);

        app(ChatService::class)->openConversation($applicant, $admin);

        $this
            ->actingAs($applicant)
            ->get(route('messages.index'))
            ->assertOk()
            ->assertSee('Platform Support')
            ->assertSee('Start the conversation');
    }

    public function test_approved_applicant_can_send_a_support_message(): void
    {
        config(['broadcasting.default' => 'null']);

        $admin = User::factory()->create(['account_type' => 'admin']);
        $applicant = User::factory()->create([
            'account_type' => 'pwd_applicant',
            'applicant_review_status' => 'approved',
        ]);

        $conversation = app(ChatService::class)->openConversation($applicant, $admin);

        $response = $this
            ->actingAs($applicant)
            ->postJson(route('messages.store', $conversation), [
                'body' => 'Hello, I need help with my profile.',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('message.conversation_id', $conversation->id)
            ->assertJsonPath('message.body', 'Hello, I need help with my profile.');

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $applicant->id,
            'body' => 'Hello, I need help with my profile.',
        ]);

    }

    public function test_employer_can_continue_an_application_chat_when_documents_expire(): void
    {
        config(['broadcasting.default' => 'null']);

        $employer = User::factory()->create([
            'account_type' => 'employer',
            'employer_document_status' => 'expired',
        ]);
        $applicant = User::factory()->create([
            'account_type' => 'pwd_applicant',
            'applicant_review_status' => 'approved',
        ]);
        $job = Job::query()->create([
            'user_id' => $employer->id,
            'title' => 'Customer Support Associate',
            'location' => 'Dasmarinas, Cavite',
            'employment_type' => 'full_time',
            'vacancies' => 1,
            'description' => 'Provide accessible customer support.',
            'status' => 'published',
        ]);
        $application = JobApplication::query()->create([
            'job_id' => $job->id,
            'applicant_id' => $applicant->id,
            'status' => 'applied',
        ]);
        $conversation = app(ChatService::class)->openApplicationConversation($application);

        $this
            ->actingAs($employer)
            ->get(route('messages.index', ['conversation' => $conversation->id]))
            ->assertOk()
            ->assertSee('data-chat-send-form', false)
            ->assertSee('Write a message...', false);

        $this
            ->actingAs($employer)
            ->postJson(route('messages.store', $conversation), ['body' => 'Thank you for applying.'])
            ->assertCreated()
            ->assertJsonPath('message.body', 'Thank you for applying.');
    }

    public function test_only_the_job_owner_can_update_the_hiring_stage_in_an_application_chat(): void
    {
        $employer = User::factory()->create(['account_type' => 'employer']);
        $applicant = User::factory()->create([
            'account_type' => 'pwd_applicant',
            'applicant_review_status' => 'approved',
        ]);
        $job = Job::query()->create([
            'user_id' => $employer->id,
            'title' => 'Administrative Assistant',
            'location' => 'Dasmarinas, Cavite',
            'employment_type' => 'full_time',
            'vacancies' => 1,
            'description' => 'Support daily office operations.',
            'status' => 'published',
        ]);
        $application = JobApplication::query()->create([
            'job_id' => $job->id,
            'applicant_id' => $applicant->id,
            'status' => 'applied',
        ]);
        $conversation = app(ChatService::class)->openApplicationConversation($application);

        $this
            ->actingAs($employer)
            ->patchJson(route('messages.application-stage', $conversation), [
                'status' => 'interview_schedule',
            ])
            ->assertOk()
            ->assertJsonPath('application_timeline.status', 'interview_schedule')
            ->assertJsonPath('application_timeline.number', 3)
            ->assertJsonPath('application_timeline.title', 'Interview schedule');

        $this->assertDatabaseHas('job_applications', [
            'id' => $application->id,
            'status' => 'interview_schedule',
        ]);

        $this
            ->actingAs($applicant)
            ->patchJson(route('messages.application-stage', $conversation), [
                'status' => 'employment_offer',
            ])
            ->assertForbidden();

        $this
            ->actingAs($applicant)
            ->getJson(route('messages.updates', $conversation))
            ->assertOk()
            ->assertJsonPath('application_timeline.status', 'interview_schedule')
            ->assertJsonPath('application_timeline.number', 3);
    }

    public function test_applicant_can_confirm_an_employer_requirements_card(): void
    {
        config(['broadcasting.default' => 'null']);

        $employer = User::factory()->create(['account_type' => 'employer']);
        $applicant = User::factory()->create([
            'account_type' => 'pwd_applicant',
            'applicant_review_status' => 'approved',
        ]);
        $job = Job::query()->create([
            'user_id' => $employer->id,
            'title' => 'Data Entry Associate',
            'location' => 'Dasmarinas, Cavite',
            'employment_type' => 'full_time',
            'vacancies' => 1,
            'description' => 'Maintain accessible data records.',
            'application_requirements' => "Resume o CV\nValid ID",
            'status' => 'published',
        ]);
        $application = JobApplication::query()->create([
            'job_id' => $job->id,
            'applicant_id' => $applicant->id,
            'status' => 'applied',
        ]);
        $conversation = app(ChatService::class)->openApplicationConversation($application);
        $card = app(ChatService::class)->sendRequirementsCard($employer, $conversation, $application);

        $this
            ->actingAs($applicant)
            ->postJson(route('messages.requirements.acknowledge', [
                'conversation' => $conversation,
                'message' => $card,
            ]))
            ->assertOk()
            ->assertJsonPath('requirements_card.message_type', Message::TYPE_REQUIREMENTS_CARD)
            ->assertJsonPath('requirements_card.metadata.acknowledged_by', $applicant->id)
            ->assertJsonPath('application_timeline.status', 'application_review')
            ->assertJsonPath('reply.body', 'Nabasa ko po ang mga requirement. Ihahanda ko po ang mga ito.');

        $this->assertNotEmpty($card->fresh()->metadata['acknowledged_at']);
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $applicant->id,
            'body' => 'Nabasa ko po ang mga requirement. Ihahanda ko po ang mga ito.',
            'message_type' => Message::TYPE_TEXT,
        ]);
        $this->assertDatabaseHas('job_applications', [
            'id' => $application->id,
            'status' => 'application_review',
        ]);

        $this
            ->actingAs($applicant)
            ->postJson(route('messages.requirements.acknowledge', [
                'conversation' => $conversation,
                'message' => $card,
            ]))
            ->assertOk()
            ->assertJsonPath('reply', null);

        $this->assertDatabaseCount('messages', 2);

        $this
            ->actingAs($employer)
            ->postJson(route('messages.requirements.acknowledge', [
                'conversation' => $conversation,
                'message' => $card,
            ]))
            ->assertForbidden();
    }

    public function test_employer_can_send_a_structured_interview_card_from_hiring_actions(): void
    {
        config(['broadcasting.default' => 'null']);

        $employer = User::factory()->create(['account_type' => 'employer']);
        $applicant = User::factory()->create([
            'account_type' => 'pwd_applicant',
            'applicant_review_status' => 'approved',
        ]);
        $job = Job::query()->create([
            'user_id' => $employer->id,
            'title' => 'Customer Support Associate',
            'location' => 'Dasmarinas, Cavite',
            'employment_type' => 'full_time',
            'vacancies' => 1,
            'description' => 'Provide accessible customer support.',
            'status' => 'published',
        ]);
        $application = JobApplication::query()->create([
            'job_id' => $job->id,
            'applicant_id' => $applicant->id,
            'status' => 'application_review',
        ]);
        $conversation = app(ChatService::class)->openApplicationConversation($application);

        $this
            ->actingAs($employer)
            ->postJson(route('messages.hiring-actions.store', $conversation), [
                'action' => 'interview',
                'interview_date' => '2026-08-14',
                'interview_time' => '14:00',
                'interview_method' => 'Video call',
                'meeting_link' => 'https://meet.example.test/interview',
                'details' => 'Ihanda ang iyong resume.',
            ])
            ->assertCreated()
            ->assertJsonPath('message.message_type', Message::TYPE_HIRING_ACTION)
            ->assertJsonPath('message.metadata.action', 'interview')
            ->assertJsonPath('message.metadata.title', 'Iskedyul ng interview')
            ->assertJsonPath('application_timeline.status', 'interview_schedule');

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $employer->id,
            'message_type' => Message::TYPE_HIRING_ACTION,
        ]);
        $this->assertDatabaseHas('job_applications', [
            'id' => $application->id,
            'status' => 'interview_schedule',
        ]);

        $this
            ->actingAs($applicant)
            ->postJson(route('messages.hiring-actions.store', $conversation), [
                'action' => 'assessment',
                'assessment_title' => 'Typing test',
                'assessment_instructions' => 'Complete the assessment.',
            ])
            ->assertForbidden();
    }

    public function test_participant_can_check_their_contact_presence(): void
    {
        $admin = User::factory()->create(['account_type' => 'admin']);
        $admin->forceFill(['last_seen_at' => now()])->save();
        $applicant = User::factory()->create([
            'account_type' => 'pwd_applicant',
            'applicant_review_status' => 'approved',
        ]);
        $conversation = app(ChatService::class)->openConversation($applicant, $admin);

        $this
            ->actingAs($applicant)
            ->getJson(route('messages.presence', $conversation))
            ->assertOk()
            ->assertJsonPath('last_seen_at', $admin->fresh()->last_seen_at?->toIso8601String());
    }

    public function test_other_applicant_cannot_open_or_send_in_someone_elses_conversation(): void
    {
        $admin = User::factory()->create(['account_type' => 'admin']);
        $firstApplicant = User::factory()->create([
            'account_type' => 'pwd_applicant',
            'applicant_review_status' => 'approved',
        ]);
        $otherApplicant = User::factory()->create([
            'account_type' => 'pwd_applicant',
            'applicant_review_status' => 'approved',
        ]);
        $conversation = app(ChatService::class)->openConversation($firstApplicant, $admin);

        $this
            ->actingAs($otherApplicant)
            ->get(route('messages.index', ['conversation' => $conversation->id]))
            ->assertNotFound();

        $this
            ->actingAs($otherApplicant)
            ->postJson(route('messages.store', $conversation), ['body' => 'Not allowed'])
            ->assertNotFound();

        $this
            ->actingAs($otherApplicant)
            ->patchJson(route('messages.read', $conversation))
            ->assertNotFound();
    }
}
