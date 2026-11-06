<?php

declare(strict_types=1);

namespace App\Modules\News\Application\UseCases;

use App\Modules\News\Application\Mail\LaunchAnnouncement;
use App\Modules\News\Infrastructure\Persistence\Models\LaunchSubscriber;
use Illuminate\Support\Facades\Mail;
use LogicException;
use Throwable;

final class SendLaunchAnnouncements
{
    public function pendingCount(): int
    {
        return LaunchSubscriber::query()->whereNull('notified_at')->count();
    }

    /** @return array{sent: int, failed: int} */
    public function execute(): array
    {
        if (config('game.prelaunch')) {
            throw new LogicException('Сначала откройте игру: GAME_PRELAUNCH=false.');
        }

        $sent = 0;
        $failed = 0;
        foreach (LaunchSubscriber::query()->whereNull('notified_at')->lazyById(100) as $subscriber) {
            try {
                Mail::to($subscriber->email)->locale($subscriber->locale)->send(new LaunchAnnouncement);
                $subscriber->forceFill(['notified_at' => now()])->save();
                $sent++;
            } catch (Throwable $exception) {
                report($exception);
                $failed++;
            }
        }

        return ['sent' => $sent, 'failed' => $failed];
    }
}
