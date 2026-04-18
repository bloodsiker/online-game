<?php

declare(strict_types=1);

namespace App\Modules\Monster;

use App\Modules\Monster\Domain\Contracts\MonsterReadRepository;
use App\Modules\Monster\Infrastructure\Persistence\EloquentMonsterReadRepository;
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
        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'monster');

        Route::middleware(['web'])
            ->group(__DIR__.'/Presentation/Http/Route/web.php');
    }
}
