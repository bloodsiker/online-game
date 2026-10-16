<?php

declare(strict_types=1);

namespace App\Modules\Auth\Application\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

final class GameEmailVerificationNotification extends Notification
{
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes((int) config('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ],
        );

        return (new MailMessage)
            ->subject(__('auth.email_verification.subject'))
            ->view('emails.auth.verify-email', [
                'verificationUrl' => $verificationUrl,
                'playerName' => $notifiable->name,
            ]);
    }
}
