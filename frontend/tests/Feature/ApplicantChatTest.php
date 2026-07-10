<?php

namespace Tests\Feature;

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
            ->assertForbidden();

        $this
            ->actingAs($otherApplicant)
            ->postJson(route('messages.store', $conversation), ['body' => 'Not allowed'])
            ->assertForbidden();
    }
}
