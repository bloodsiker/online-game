<?php

declare(strict_types=1);

namespace App\Modules\Event;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'event');

        Route::middleware(['web'])
            ->group(__DIR__.'/Presentation/Http/Route/web.php');

        Route::middleware(['web', 'isAdmin'])
            ->prefix('admin')
            ->name('admin.')
            ->group(__DIR__.'/Presentation/Http/Route/admin.php');
    }
}
