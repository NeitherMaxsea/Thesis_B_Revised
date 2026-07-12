<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BroadcastChannelAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'broadcasting.default' => 'reverb',
            'broadcasting.connections.reverb' => [
                'driver' => 'reverb',
                'key' => 'test-key',
                'secret' => 'test-secret',
                'app_id' => 'test-app',
                'options' => [
                    'host' => '127.0.0.1',
                    'port' => 8080,
                    'scheme' => 'http',
                    'useTLS' => false,
                ],
                'client_options' => [],
            ],
        ]);

        app(BroadcastManager::class)->forgetDrivers();
        require base_path('routes/channels.php');
    }

    public function test_user_can_authorize_only_their_own_private_user_channel(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $this
            ->actingAs($user)
            ->post('/broadcasting/auth', $this->authorizationPayload(
                "private-App.Models.User.{$user->id}"
            ))
            ->assertOk()
            ->assertJsonStructure(['auth']);

        $this
            ->actingAs($user)
            ->post('/broadcasting/auth', $this->authorizationPayload(
                "private-App.Models.User.{$otherUser->id}"
            ))
            ->assertForbidden();
    }

    public function test_only_conversation_participants_can_authorize_its_private_channel(): void
    {
        $firstParticipant = User::factory()->create();
        $secondParticipant = User::factory()->create();
        $outsider = User::factory()->create();
        [$firstUserId, $secondUserId] = collect([
            $firstParticipant->id,
            $secondParticipant->id,
        ])->sort()->values()->all();
        $conversation = Conversation::create([
            'first_user_id' => $firstUserId,
            'second_user_id' => $secondUserId,
        ]);
        $channel = "private-chat.conversation.{$conversation->id}";

        $this
            ->actingAs($firstParticipant)
            ->post('/broadcasting/auth', $this->authorizationPayload($channel))
            ->assertOk()
            ->assertJsonStructure(['auth']);

        $this
            ->actingAs($secondParticipant)
            ->post('/broadcasting/auth', $this->authorizationPayload($channel))
            ->assertOk()
            ->assertJsonStructure(['auth']);

        $this
            ->actingAs($outsider)
            ->post('/broadcasting/auth', $this->authorizationPayload($channel))
            ->assertForbidden();
    }

    public function test_only_admins_can_authorize_live_account_notifications(): void
    {
        $admin = User::factory()->create(['account_type' => 'admin']);
        $applicant = User::factory()->create(['account_type' => 'pwd_applicant']);
        $channel = 'private-admin.accounts';

        $this
            ->actingAs($admin)
            ->post('/broadcasting/auth', $this->authorizationPayload($channel))
            ->assertOk()
            ->assertJsonStructure(['auth']);

        $this
            ->actingAs($applicant)
            ->post('/broadcasting/auth', $this->authorizationPayload($channel))
            ->assertForbidden();
    }

    public function test_guest_cannot_authorize_a_private_channel(): void
    {
        $user = User::factory()->create();

        $this
            ->post('/broadcasting/auth', $this->authorizationPayload(
                "private-App.Models.User.{$user->id}"
            ))
            ->assertForbidden();
    }

    /**
     * @return array{socket_id: string, channel_name: string}
     */
    private function authorizationPayload(string $channelName): array
    {
        return [
            'socket_id' => '1234.5678',
            'channel_name' => $channelName,
        ];
    }
}
