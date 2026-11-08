<?php

declare(strict_types=1);

namespace App\Modules\Telegram\Application\Listeners;

use App\Modules\News\Domain\Events\LaunchSubscribed;
use App\Modules\Telegram\Application\Jobs\SendAdminTelegramMessage;

final class NotifyAdminAboutLaunchSubscription
{
    public function handle(LaunchSubscribed $event): void
    {
        if (! SendAdminTelegramMessage::enabled()) {
            return;
        }

        SendAdminTelegramMessage::dispatch(implode("\n", [
            '📝 <b>Предрегистрация</b>',
            '',
            '✉️ '.e($event->email),
            '🌐 '.strtoupper($event->locale),
            '',
            '📊 Всего в списке: '.$event->subscribersTotal,
        ]));
    }
}
