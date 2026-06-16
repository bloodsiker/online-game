<?php

declare(strict_types=1);

namespace App\Modules\Dungeon;

use App\Modules\Dungeon\Domain\Contracts\DungeonCooldownRepository;
use App\Modules\Dungeon\Domain\Contracts\DungeonReadRepository;
use App\Modules\Dungeon\Domain\Contracts\DungeonSessionRepository;
use App\Modules\Dungeon\Domain\Contracts\TransactionManager;
use App\Modules\Dungeon\Infrastructure\Persistence\EloquentDungeonCooldownRepository;
use App\Modules\Dungeon\Infrastructure\Persistence\EloquentDungeonReadRepository;
use App\Modules\Dungeon\Infrastructure\Persistence\EloquentDungeonSessionRepository;
use App\Modules\Dungeon\Infrastructure\Persistence\LaravelTransactionManager;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class DungeonServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(DungeonCooldownRepository::class, EloquentDungeonCooldownRepository::class);
        $this->app->bind(DungeonReadRepository::class, EloquentDungeonReadRepository::class);
        $this->app->bind(DungeonSessionRepository::class, EloquentDungeonSessionRepository::class);
        $this->app->bind(TransactionManager::class, LaravelTransactionManager::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'dungeon');

        Route::middleware(['web'])
            ->group(__DIR__.'/Presentation/Http/Route/web.php');
    }
}
