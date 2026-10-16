<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class ClanApplicationReceivedNotification extends Notification
{
    public function __construct(
        private readonly string $clanName,
        private readonly string $applicantName,
        private readonly int $applicantLevel,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('clan.application.mail_subject', ['clan' => $this->clanName]))
            ->view('emails.clan.application-received', [
                'leaderName' => $notifiable->name,
                'clanName' => $this->clanName,
                'applicantName' => $this->applicantName,
                'applicantLevel' => $this->applicantLevel,
                'applicationsUrl' => route('clan.member'),
            ]);
    }
}
