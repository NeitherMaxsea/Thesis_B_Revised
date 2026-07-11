<?php

namespace Tests\Feature;

use App\Events\MessagesRead;
use App\Models\Conversation;
use App\Models\EmployerDocument;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\Message;
use App\Models\User;
use App\Services\ChatService;
use App\Services\EmployerDocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RealtimeChatTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['broadcasting.default' => 'null']);
    }

    public function test_recipient_can_mark_incoming_messages_read_and_a_seen_receipt_is_emitted(): void
    {
        $applicant = $this->createApprovedApplicant();
        $employer = $this->createVerifiedEmployer();
        [, , $conversation] = $this->createApplicationConversation(
            $applicant,
            $employer,
            'Accessible Customer Support'
        );

        $conversation->messages()->create([
            'sender_id' => $employer->id,
            'body' => 'Your interview is scheduled for tomorrow.',
        ]);
        $outgoingMessage = $conversation->messages()->create([
            'sender_id' => $applicant->id,
            'body' => 'Thank you. I will be ready.',
        ]);

        $incomingMessageIds = $conversation->messages()
            ->where('sender_id', $employer->id)
            ->whereNull('read_at')
            ->orderBy('id')
            ->pluck('id')
            ->all();

        $this->assertNotEmpty($incomingMessageIds);

        $conversationForApplicant = app(ChatService::class)
            ->conversationsFor($applicant)
            ->firstWhere('id', $conversation->id);

        $this->assertNotNull($conversationForApplicant);
        $this->assertSame(count($incomingMessageIds), (int) $conversationForApplicant->unread_count);
        $this->assertSame(count($incomingMessageIds), app(ChatService::class)->totalUnreadCount($applicant));

        Event::fake([MessagesRead::class]);

        $response = $this
            ->actingAs($applicant)
            ->patchJson(route('messages.read', $conversation));

        $response
            ->assertOk()
            ->assertJsonPath('read.conversation_id', $conversation->id)
            ->assertJsonPath('read.reader_id', $applicant->id)
            ->assertJsonPath('read.message_ids', $incomingMessageIds)
            ->assertJsonPath('unread_message_count', 0);

        $this->assertNotNull($response->json('read.read_at'));

        foreach ($incomingMessageIds as $messageId) {
            $this->assertNotNull(Message::findOrFail($messageId)->read_at);
        }

        $this->assertNull($outgoingMessage->fresh()->read_at);
        $this->assertSame(0, app(ChatService::class)->totalUnreadCount($applicant));

        Event::assertDispatched(MessagesRead::class, function (MessagesRead $event) use (
            $applicant,
            $conversation,
            $incomingMessageIds
        ): bool {
            $receipt = $event->broadcastWith();

            return $receipt['conversation_id'] === $conversation->id
                && $receipt['reader_id'] === $applicant->id
                && $receipt['message_ids'] === $incomingMessageIds
                && is_string($receipt['read_at'])
                && $receipt['read_at'] !== '';
        });

        $this
            ->actingAs($applicant)
            ->patchJson(route('messages.read', $conversation))
            ->assertOk()
            ->assertJsonPath('read.message_ids', [])
            ->assertJsonPath('unread_message_count', 0);

        Event::assertDispatchedTimes(MessagesRead::class, 1);
    }

    public function test_loading_a_conversation_leaves_read_writes_to_the_csrf_protected_endpoint(): void
    {
        $applicant = $this->createApprovedApplicant();
        $employer = $this->createVerifiedEmployer();
        [, , $conversation] = $this->createApplicationConversation(
            $applicant,
            $employer,
            'Office Administration Assistant'
        );
        $conversation->messages()->create([
            'sender_id' => $employer->id,
            'body' => 'Please confirm your interview availability.',
        ]);
        $outgoingMessage = $conversation->messages()->create([
            'sender_id' => $applicant->id,
            'body' => 'I am available tomorrow morning.',
        ]);
        $incomingMessageIds = $conversation->messages()
            ->where('sender_id', $employer->id)
            ->whereNull('read_at')
            ->orderBy('id')
            ->pluck('id')
            ->all();

        $this->assertNotEmpty($incomingMessageIds);
        Event::fake([MessagesRead::class]);

        $this
            ->actingAs($applicant)
            ->get(route('messages.index', ['conversation' => $conversation->id]))
            ->assertOk()
            ->assertViewHas('unreadMessageCount', count($incomingMessageIds));

        foreach ($incomingMessageIds as $messageId) {
            $this->assertNull(Message::findOrFail($messageId)->read_at);
        }

        $this->assertNull($outgoingMessage->fresh()->read_at);
        $this->assertSame(
            count($incomingMessageIds),
            app(ChatService::class)->totalUnreadCount($applicant)
        );
        Event::assertNotDispatched(MessagesRead::class);
    }

    public function test_direct_conversation_start_is_idempotent_and_rejects_an_unrelated_employer(): void
    {
        $applicant = $this->createApprovedApplicant();
        $support = User::factory()->create([
            'account_type' => 'admin',
            'name' => 'Platform Support',
        ]);
        $unrelatedEmployer = $this->createVerifiedEmployer();

        $firstResponse = $this
            ->actingAs($applicant)
            ->post(route('messages.conversations.store'), [
                'recipient_id' => $support->id,
            ]);
        $conversation = Conversation::query()
            ->forParticipant($applicant->id)
            ->forParticipant($support->id)
            ->where('context_key', 'direct')
            ->firstOrFail();

        $firstResponse->assertRedirect(route('messages.index', [
            'conversation' => $conversation->id,
        ]));

        $this
            ->actingAs($applicant)
            ->post(route('messages.conversations.store'), [
                'recipient_id' => $support->id,
            ])
            ->assertRedirect(route('messages.index', [
                'conversation' => $conversation->id,
            ]));

        $this->assertSame(1, Conversation::query()
            ->forParticipant($applicant->id)
            ->forParticipant($support->id)
            ->where('context_key', 'direct')
            ->count());

        $this
            ->actingAs($applicant)
            ->post(route('messages.conversations.store'), [
                'recipient_id' => $unrelatedEmployer->id,
            ])
            ->assertForbidden();
    }

    public function test_blank_tag_only_and_overlength_messages_are_rejected_without_being_saved(): void
    {
        $applicant = $this->createApprovedApplicant();
        $employer = $this->createVerifiedEmployer();
        [, , $conversation] = $this->createApplicationConversation(
            $applicant,
            $employer,
            'Data Entry Associate'
        );
        $messageCount = $conversation->messages()->count();

        foreach (['   ', '<strong> </strong>', str_repeat('a', 2001)] as $invalidBody) {
            $this
                ->actingAs($applicant)
                ->postJson(route('messages.store', $conversation), ['body' => $invalidBody])
                ->assertUnprocessable()
                ->assertJsonValidationErrors('body');
        }

        $this->assertSame($messageCount, $conversation->messages()->count());
    }

    public function test_conversations_are_sorted_by_the_latest_message_timestamp(): void
    {
        $applicant = $this->createApprovedApplicant();
        $employer = $this->createVerifiedEmployer();
        [, , $olderConversation] = $this->createApplicationConversation(
            $applicant,
            $employer,
            'Customer Support Associate'
        );
        [, , $newerConversation] = $this->createApplicationConversation(
            $applicant,
            $employer,
            'Records Assistant'
        );

        $olderConversation->forceFill(['last_message_at' => now()->subHour()])->save();
        $newerConversation->forceFill(['last_message_at' => now()])->save();

        $this
            ->actingAs($applicant)
            ->get(route('messages.index', ['conversation' => $newerConversation->id]))
            ->assertOk()
            ->assertViewHas('conversations', function ($conversations) use (
                $newerConversation,
                $olderConversation
            ): bool {
                return $conversations->pluck('id')->all() === [
                    $newerConversation->id,
                    $olderConversation->id,
                ];
            });
    }

    private function createApprovedApplicant(): User
    {
        return User::factory()->create([
            'account_type' => 'pwd_applicant',
            'applicant_review_status' => 'approved',
        ]);
    }

    private function createVerifiedEmployer(): User
    {
        $employer = User::factory()->create([
            'account_type' => 'employer',
            'employer_document_status' => 'valid',
            'company_name' => 'Inclusive Works Inc.',
        ]);

        foreach (EmployerDocumentService::REQUIRED_DOCUMENT_TYPES as $documentType) {
            EmployerDocument::create([
                'user_id' => $employer->id,
                'document_type' => $documentType,
                'file_path' => 'employer-documents/test.pdf',
                'expires_at' => now()->addYear(),
                'status' => 'valid',
            ]);
        }

        return $employer;
    }

    /**
     * @return array{Job, JobApplication, Conversation}
     */
    private function createApplicationConversation(
        User $applicant,
        User $employer,
        string $jobTitle
    ): array {
        $job = Job::create([
            'user_id' => $employer->id,
            'title' => $jobTitle,
            'location' => 'Dasmarinas, Cavite',
            'employment_type' => 'full_time',
            'vacancies' => 1,
            'description' => "{$jobTitle} description.",
            'application_requirements' => 'Submit your current resume.',
            'status' => 'published',
        ]);

        $this
            ->actingAs($applicant)
            ->post(route('jobs.apply', $job))
            ->assertRedirect();

        $application = JobApplication::query()
            ->where('job_id', $job->id)
            ->where('applicant_id', $applicant->id)
            ->firstOrFail();
        $conversation = Conversation::query()
            ->where('job_application_id', $application->id)
            ->firstOrFail();

        return [$job, $application, $conversation];
    }
}
