<?php

declare(strict_types=1);

namespace App\Modules\Battle;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class BattleServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'battle');

        Route::middleware(['web'])
            ->group(__DIR__.'/Presentation/Http/Route/web.php');
    }
}
