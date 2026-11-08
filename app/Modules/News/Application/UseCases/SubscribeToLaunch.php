<?php

declare(strict_types=1);

namespace App\Modules\News\Application\UseCases;

use App\Modules\News\Domain\Events\LaunchSubscribed;
use App\Modules\News\Infrastructure\Persistence\Models\LaunchSubscriber;

final class SubscribeToLaunch
{
    public function execute(string $email, string $locale): void
    {
        $subscriber = LaunchSubscriber::query()->firstOrCreate(
            ['email' => mb_strtolower(trim($email))],
            ['locale' => in_array($locale, ['ru', 'uk'], true) ? $locale : 'ru'],
        );

        if ($subscriber->wasRecentlyCreated) {
            LaunchSubscribed::dispatch($subscriber->email, $subscriber->locale, LaunchSubscriber::query()->count());
        }
    }
}
