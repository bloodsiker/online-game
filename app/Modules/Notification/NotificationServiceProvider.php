<?php

declare(strict_types=1);

namespace App\Modules\Notification;

use App\Modules\Notification\Domain\Contracts\PlayerNotificationRepository;
use App\Modules\Notification\Infrastructure\Persistence\EloquentPlayerNotificationRepository;
use App\Modules\Notification\Presentation\Http\PlayerNotificationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class NotificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PlayerNotificationRepository::class, EloquentPlayerNotificationRepository::class);
    }

    public function boot(): void
    {
        Route::middleware(['web', 'auth'])->group(function (): void {
            Route::get('/player/notifications', [PlayerNotificationController::class, 'pending'])->name('player.notifications');
            Route::post('/player/notifications/acknowledge', [PlayerNotificationController::class, 'acknowledge'])->name('player.notifications.acknowledge');
        });
    }
}
