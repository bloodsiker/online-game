<?php

declare(strict_types=1);

namespace App\Modules\Effect;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class EffectServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'effect');

        Route::middleware(['web', 'isAdmin'])
            ->prefix('admin')
            ->name('admin.')
            ->group(__DIR__.'/Presentation/Http/Route/admin.php');
    }
}
