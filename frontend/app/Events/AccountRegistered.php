<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Delivers a newly-created public account to signed-in administrators.
 *
 * This intentionally uses an admin-only private channel: registration data
 * must never be published on a browser-accessible public channel.
 */
class AccountRegistered implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public User $account)
    {
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('admin.accounts')];
    }

    public function broadcastAs(): string
    {
        return 'account.registered';
    }

    public function broadcastWith(): array
    {
        return [
            'account' => self::accountPayload($this->account),
            'pending_verifications' => self::pendingVerificationCount(),
            'counts' => self::accountCounts(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function accountPayload(User $account): array
    {
        $isApplicant = $account->account_type === 'pwd_applicant';

        return [
            'id' => $account->id,
            'name' => $account->name,
            'email' => $account->email,
            'account_type' => $account->account_type,
            'role' => $isApplicant ? 'Applicant' : 'Employer',
            'review_status' => $isApplicant ? ($account->applicant_review_status ?: 'pending') : 'active',
            'created_at' => $account->created_at?->toIso8601String(),
            'created_at_display' => $account->created_at?->format('M d, Y · g:i A') ?? 'Just now',
            'date_display' => $account->created_at?->format('M d, Y') ?? 'Today',
            'contact' => $account->contact_number ?: 'Not set',
            'disability' => $account->disability ?: 'Not set',
            'age' => $account->age ?: 'Not set',
            'approve_url' => $isApplicant ? route('admin.applicants.approve', $account) : null,
            'reject_url' => $isApplicant ? route('admin.applicants.decline', $account) : null,
        ];
    }

    public static function pendingVerificationCount(): int
    {
        return User::query()
            ->where('account_type', 'pwd_applicant')
            ->where('applicant_review_status', 'pending')
            ->count();
    }

    /**
     * @return array{applicants: int, employers: int, profiles: int}
     */
    public static function accountCounts(): array
    {
        $applicants = User::query()->where('account_type', 'pwd_applicant')->count();
        $employers = User::query()->where('account_type', 'employer')->count();

        return [
            'applicants' => $applicants,
            'employers' => $employers,
            'profiles' => $applicants + $employers,
        ];
    }
}
