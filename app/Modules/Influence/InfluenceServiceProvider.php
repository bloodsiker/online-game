<?php

declare(strict_types=1);

namespace App\Modules\Influence;

use App\Modules\Influence\Domain\Services\MapInfluenceBonusService;
use App\Modules\Influence\Domain\Services\MapInfluenceRequirementService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class InfluenceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(MapInfluenceBonusService::class);
        $this->app->scoped(MapInfluenceRequirementService::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'influence');

        Route::middleware(['web'])
            ->group(__DIR__.'/Presentation/Http/Route/web.php');

        Route::middleware(['web', 'isAdmin'])
            ->prefix('admin')
            ->name('admin.')
            ->group(__DIR__.'/Presentation/Http/Route/admin.php');
    }
}
