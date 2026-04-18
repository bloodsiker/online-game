<?php

declare(strict_types=1);

namespace App\Modules\Location;

use App\Modules\Location\Domain\Contracts\LocationReadRepository;
use App\Modules\Location\Infrastructure\Persistence\EloquentLocationReadRepository;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class LocationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(LocationReadRepository::class, EloquentLocationReadRepository::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'location');

        Route::middleware(['web'])
            ->group(__DIR__.'/Presentation/Http/Route/web.php');
    }
}
