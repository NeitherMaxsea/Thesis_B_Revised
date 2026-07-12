<?php

namespace Tests\Feature;

use App\Events\MessagesRead;
use App\Models\Conversation;
use App\Models\ConversationSetting;
use App\Models\EmployerDocument;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\Message;
use App\Models\User;
use App\Services\ChatService;
use App\Services\EmployerDocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
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

    public function test_participant_can_poll_for_new_messages_and_seen_receipts_when_reverb_is_unavailable(): void
    {
        $applicant = $this->createApprovedApplicant();
        $employer = $this->createVerifiedEmployer();
        [, , $conversation] = $this->createApplicationConversation(
            $applicant,
            $employer,
            'Accessible Office Assistant'
        );
        $knownMessage = $conversation->messages()->create([
            'sender_id' => $applicant->id,
            'body' => 'I am interested in this position.',
            'read_at' => now(),
        ]);
        $newMessage = $conversation->messages()->create([
            'sender_id' => $employer->id,
            'body' => 'Thank you. Please share your available interview times.',
        ]);

        $this
            ->actingAs($applicant)
            ->getJson(route('messages.updates', [
                'conversation' => $conversation,
                'after' => $knownMessage->id,
            ]))
            ->assertOk()
            ->assertJsonCount(1, 'messages')
            ->assertJsonPath('messages.0.id', $newMessage->id)
            ->assertJsonPath('messages.0.body', $newMessage->body)
            ->assertJsonPath('read_receipts.0.id', $knownMessage->id);
    }

    public function test_participant_can_share_typing_state_with_the_other_chat_participant(): void
    {
        Cache::flush();

        $applicant = $this->createApprovedApplicant();
        $employer = $this->createVerifiedEmployer();
        [, , $conversation] = $this->createApplicationConversation(
            $applicant,
            $employer,
            'Inclusive Receptionist'
        );

        $this
            ->actingAs($employer)
            ->postJson(route('messages.typing', $conversation))
            ->assertNoContent();

        $this
            ->actingAs($applicant)
            ->getJson(route('messages.updates', $conversation))
            ->assertOk()
            ->assertJsonPath('typing.user_id', $employer->id)
            ->assertJsonPath('typing.is_typing', true);

        Cache::flush();
    }

    public function test_participant_can_archive_a_conversation_without_hiding_it_for_the_other_participant(): void
    {
        $applicant = $this->createApprovedApplicant();
        $employer = $this->createVerifiedEmployer();
        [, , $conversation] = $this->createApplicationConversation(
            $applicant,
            $employer,
            'Archive-safe Customer Support'
        );

        $this
            ->actingAs($applicant)
            ->postJson(route('messages.archive', $conversation))
            ->assertOk()
            ->assertJsonPath('archived', true);

        $this->assertNotNull(ConversationSetting::query()
            ->where('conversation_id', $conversation->id)
            ->where('user_id', $applicant->id)
            ->value('archived_at'));
        $this->assertFalse(app(ChatService::class)
            ->conversationsFor($applicant)
            ->contains('id', $conversation->id));
        $this->assertTrue(app(ChatService::class)
            ->conversationsFor($employer)
            ->contains('id', $conversation->id));

        app(ChatService::class)->send($employer, $conversation, 'A new message restores the archived chat.');

        $this->assertTrue(app(ChatService::class)
            ->conversationsFor($applicant)
            ->contains('id', $conversation->id));
    }

    public function test_deleted_conversations_stay_deleted_and_start_again_creates_a_clean_thread(): void
    {
        $applicant = $this->createApprovedApplicant();
        $employer = $this->createVerifiedEmployer();
        [, , $conversation] = $this->createApplicationConversation(
            $applicant,
            $employer,
            'Delete-safe Customer Support'
        );

        $this
            ->actingAs($applicant)
            ->deleteJson(route('messages.delete', $conversation))
            ->assertOk()
            ->assertJsonPath('deleted', true);

        $this->assertNotNull(ConversationSetting::query()
            ->where('conversation_id', $conversation->id)
            ->where('user_id', $applicant->id)
            ->value('deleted_at'));
        $this->assertFalse(app(ChatService::class)
            ->conversationsFor($applicant)
            ->contains('id', $conversation->id));
        $this->assertTrue(app(ChatService::class)
            ->conversationsFor($employer)
            ->contains('id', $conversation->id));

        app(ChatService::class)->send($employer, $conversation, 'This must not restore the deleted history.');

        $this->assertFalse(app(ChatService::class)
            ->conversationsFor($applicant)
            ->contains('id', $conversation->id));

        $this
            ->actingAs($applicant)
            ->post(route('messages.conversations.store'), ['recipient_id' => $employer->id])
            ->assertRedirect();

        $newConversation = Conversation::query()
            ->forParticipant($applicant->id)
            ->forParticipant($employer->id)
            ->where('id', '!=', $conversation->id)
            ->where(fn ($query) => $query
                ->where('context_key', 'direct')
                ->orWhere('context_key', 'like', 'direct:%'))
            ->latest('id')
            ->firstOrFail();

        $this->assertSame(0, $newConversation->messages()->count());
        $this->assertTrue(app(ChatService::class)
            ->conversationsFor($applicant)
            ->contains('id', $newConversation->id));
    }

    public function test_recipient_can_poll_the_inbox_and_receive_a_new_conversation_without_refreshing(): void
    {
        $applicant = $this->createApprovedApplicant();
        $employer = $this->createVerifiedEmployer();
        [, , $conversation] = $this->createApplicationConversation(
            $applicant,
            $employer,
            'Live Conversation Updates'
        );
        $previousMessageId = (int) (Message::max('id') ?? 0);
        $message = $conversation->messages()->create([
            'sender_id' => $employer->id,
            'body' => 'This should appear in the inbox right away.',
        ]);

        $this
            ->actingAs($applicant)
            ->getJson(route('messages.inbox-updates', ['after' => $previousMessageId]))
            ->assertOk()
            ->assertJsonPath('messages.0.id', $message->id)
            ->assertJsonPath('messages.0.conversation_id', $conversation->id)
            ->assertJsonPath('messages.0.sender.account_type', 'employer')
            ->assertJsonPath('next_after', $message->id);
    }

    public function test_participant_can_mute_and_unmute_a_conversation(): void
    {
        $applicant = $this->createApprovedApplicant();
        $employer = $this->createVerifiedEmployer();
        [, , $conversation] = $this->createApplicationConversation(
            $applicant,
            $employer,
            'Quiet Communications Assistant'
        );

        $this
            ->actingAs($applicant)
            ->patchJson(route('messages.mute', $conversation), ['duration' => '1h'])
            ->assertOk()
            ->assertJsonPath('muted_until', fn ($value) => is_string($value) && $value !== '');

        $this->assertNotNull(ConversationSetting::query()
            ->where('conversation_id', $conversation->id)
            ->where('user_id', $applicant->id)
            ->value('muted_until'));

        $this
            ->actingAs($applicant)
            ->patchJson(route('messages.mute', $conversation), ['duration' => 'off'])
            ->assertOk()
            ->assertJsonPath('muted_until', null);
    }

    public function test_participant_can_toggle_an_allowed_message_reaction(): void
    {
        $applicant = $this->createApprovedApplicant();
        $employer = $this->createVerifiedEmployer();
        [, , $conversation] = $this->createApplicationConversation(
            $applicant,
            $employer,
            'Accessible Data Entry'
        );
        $message = $conversation->messages()->create([
            'sender_id' => $employer->id,
            'body' => 'Thank you for applying.',
        ]);

        $this
            ->actingAs($applicant)
            ->postJson(route('messages.reactions', [
                'conversation' => $conversation,
                'message' => $message,
            ]), ['emoji' => '👍'])
            ->assertOk()
            ->assertJsonPath('message_id', $message->id)
            ->assertJsonPath('reactions.0.emoji', '👍')
            ->assertJsonPath('reactions.0.count', 1)
            ->assertJsonPath('reactions.0.user_ids.0', $applicant->id);

        $this
            ->actingAs($applicant)
            ->postJson(route('messages.reactions', [
                'conversation' => $conversation,
                'message' => $message,
            ]), ['emoji' => '❤️'])
            ->assertOk()
            ->assertJsonPath('reactions.0.emoji', '❤️')
            ->assertJsonPath('reactions.0.count', 1);

        $this->assertSame(1, $message->reactions()->count());

        $this
            ->actingAs($applicant)
            ->postJson(route('messages.reactions', [
                'conversation' => $conversation,
                'message' => $message,
            ]), ['emoji' => '❤️'])
            ->assertOk()
            ->assertJsonCount(0, 'reactions');
    }

    public function test_participant_can_send_a_private_image_attachment_with_an_optional_caption(): void
    {
        Storage::fake('local');

        $applicant = $this->createApprovedApplicant();
        $employer = $this->createVerifiedEmployer();
        [, , $conversation] = $this->createApplicationConversation(
            $applicant,
            $employer,
            'Accessible Media Assistant'
        );
        $attachment = UploadedFile::fake()->create('portfolio.png', 120, 'image/png');

        $response = $this
            ->actingAs($applicant)
            ->post(route('messages.store', $conversation), [
                'body' => 'Here is my sample portfolio image.',
                'attachment' => $attachment,
            ], [
                'Accept' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('message.body', 'Here is my sample portfolio image.')
            ->assertJsonPath('message.attachment.mime', 'image/png')
            ->assertJsonPath('message.attachment.name', 'portfolio.png');

        $message = Message::query()->latest('id')->firstOrFail();
        $this->assertNotNull($message->attachment_path);
        Storage::disk('local')->assertExists($message->attachment_path);

        $this
            ->actingAs($employer)
            ->get(route('messages.attachment', [
                'conversation' => $conversation,
                'message' => $message,
            ]))
            ->assertOk()
            ->assertHeader('content-type', 'image/png');
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
