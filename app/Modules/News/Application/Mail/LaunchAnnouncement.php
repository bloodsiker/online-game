<?php

declare(strict_types=1);

namespace App\Modules\News\Application\Mail;

use Illuminate\Mail\Mailable;

final class LaunchAnnouncement extends Mailable
{
    public function build(): self
    {
        return $this->subject(__('prelaunch.mail_subject'))
            ->view('news::emails.launch', ['registrationUrl' => route('register')]);
    }
}
