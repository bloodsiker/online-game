<?php

declare(strict_types=1);

namespace App\Modules\Trade;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class TradeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'trade');
        Route::middleware(['web', 'auth', 'prison.context'])->group(__DIR__.'/Presentation/Http/Route/web.php');
        Route::middleware(['web', 'isAdmin'])->prefix('admin')->name('admin.')
            ->group(__DIR__.'/Presentation/Http/Route/admin.php');
    }
}
