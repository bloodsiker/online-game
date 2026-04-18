<?php

declare(strict_types=1);

namespace App\Modules\Rating;

use App\Modules\Rating\Domain\Contracts\RatingReadRepository;
use App\Modules\Rating\Infrastructure\Persistence\EloquentRatingReadRepository;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class RatingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(RatingReadRepository::class, EloquentRatingReadRepository::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'rating');

        Route::middleware(['web'])
            ->group(__DIR__.'/Presentation/Http/Route/web.php');
    }
}
