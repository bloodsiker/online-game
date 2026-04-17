<?php

declare(strict_types=1);

namespace App\Modules\Backpack;

use App\Modules\Backpack\Domain\Repositories\BackpackRepositoryInterface;
use App\Modules\Backpack\Infrastructure\Persistence\EloquentBackpackRepository;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class BackpackServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            BackpackRepositoryInterface::class,
            EloquentBackpackRepository::class,
        );
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'backpack');

        Route::middleware(['web'])
            ->group(__DIR__.'/Presentation/Http/Route/web.php');
    }
}
