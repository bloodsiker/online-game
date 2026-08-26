<?php

declare(strict_types=1);

namespace App\Modules\Monster;

use App\Modules\Monster\Domain\Contracts\MonsterReadRepository;
use App\Modules\Monster\Infrastructure\Persistence\EloquentMonsterReadRepository;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Monster\Infrastructure\Persistence\Observers\MonsterObserver;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class MonsterServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MonsterReadRepository::class, EloquentMonsterReadRepository::class);
    }

    public function boot(): void
    {
        Monster::observe(MonsterObserver::class);

        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'monster');

        Route::middleware(['web'])
            ->group(__DIR__.'/Presentation/Http/Route/web.php');
    }
}
