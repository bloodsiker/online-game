<?php

declare(strict_types=1);

namespace App\Modules\Player;

use App\Modules\Player\Application\Listeners\RecalculatePlayerModification;
use App\Modules\Player\Application\Listeners\RecalculatePlayerStats;
use App\Modules\Player\Application\Listeners\RemoveExpOnDeathListener;
use App\Modules\Player\Domain\Events\PlayerChangeStat;
use App\Modules\Player\Domain\Events\PlayerDied;
use App\Modules\Player\Domain\Events\PlayerLeveledUp;
use App\Modules\Player\Domain\Repositories\PlayerRepositoryInterface;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Player\Infrastructure\Persistence\EloquentPlayerRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class PlayerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // One stat sheet cache per HTTP request / queue job (Octane-safe).
        $this->app->scoped(PlayerStatService::class);

        $this->app->bind(
            PlayerRepositoryInterface::class,
            EloquentPlayerRepository::class,
        );
    }

    public function boot(): void
    {
        Event::listen(PlayerChangeStat::class, RecalculatePlayerModification::class);
        Event::listen(PlayerDied::class, RemoveExpOnDeathListener::class);
        Event::listen(PlayerLeveledUp::class, RecalculatePlayerStats::class);

        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'player');

        Route::middleware(['web'])
            ->group(__DIR__.'/Presentation/Http/Route/web.php');
    }
}
