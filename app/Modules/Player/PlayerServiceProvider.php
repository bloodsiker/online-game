<?php

declare(strict_types=1);

namespace App\Modules\Player;

use App\Modules\Player\Domain\Repositories\PlayerRepositoryInterface;
use App\Modules\Player\Infrastructure\Persistence\EloquentPlayerRepository;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class PlayerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            PlayerRepositoryInterface::class,
            EloquentPlayerRepository::class,
        );
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'player');

        Route::middleware(['web'])
            ->group(__DIR__.'/Presentation/Http/Route/web.php');
    }
}
