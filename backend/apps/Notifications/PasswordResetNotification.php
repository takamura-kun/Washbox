<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetNotification extends Notification
{
    use Queueable;

    private string $resetCode;

    public function __construct(string $resetCode)
    {
        $this->resetCode = $resetCode;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('WashBox - Password Reset Code')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You are receiving this email because we received a password reset request for your account.')
            ->line('Your password reset code is: **' . $this->resetCode . '**')
            ->line('This code will expire in 15 minutes.')
            ->line('If you did not request a password reset, no further action is required.');
    }
}