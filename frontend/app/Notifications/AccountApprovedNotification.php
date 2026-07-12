<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly User $account)
    {
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
            ->subject('Your PWD Employment account has been approved')
            ->greeting("Hello {$recipient},")
            ->line('Your account verification has been approved. You can now sign in and use the platform.')
            ->action('Login', route('login'))
            ->line('If you did not create this account, you can safely ignore this email.');
    }
}
