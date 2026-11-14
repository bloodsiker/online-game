<?php

declare(strict_types=1);

namespace App\Modules\Gift;

use App\Modules\Gift\Domain\Contracts\PlayerGiftRepository;
use App\Modules\Gift\Infrastructure\Persistence\EloquentPlayerGiftRepository;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/** Подарки: игрок дарит предмет типа «gift» с подписью, подарки видны в публичной информации получателя. */
class GiftServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PlayerGiftRepository::class, EloquentPlayerGiftRepository::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'gift');

        Route::middleware(['web'])->group(__DIR__.'/Presentation/Http/Route/web.php');
    }
}
