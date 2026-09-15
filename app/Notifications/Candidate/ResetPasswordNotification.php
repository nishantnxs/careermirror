<?php

namespace App\Notifications\Candidate;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(public string $token) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url(route('candidate.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Reset your candidate password')
            ->line('You are receiving this email because we received a password reset request for your candidate account.')
            ->action('Reset Password', $url)
            ->line('This password reset link will expire in '.config('auth.passwords.candidates.expire').' minutes.')
            ->line('If you did not request a password reset, no further action is required.');
    }
}
