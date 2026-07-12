<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly User $account,
        private readonly string $reason,
    ) {
    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $recipient = $this->account->first_name ?: $this->account->name;

        return (new MailMessage)
            ->subject('Your PWD Employment account verification was rejected')
            ->greeting("Hello {$recipient},")
            ->line('We were unable to approve your account verification at this time.')
            ->line("Reason: {$this->reason}")
            ->line('Please correct the issue and contact the platform administrator if you need help.');
    }
}
