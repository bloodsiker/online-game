<?php

declare(strict_types=1);

namespace App\Modules\News;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class NewsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'news');

        Route::middleware(['web'])
            ->group(__DIR__.'/Presentation/Http/Route/web.php');

        Route::middleware(['web', 'isAdmin'])
            ->prefix('admin')
            ->name('admin.')
            ->group(__DIR__.'/Presentation/Http/Route/admin.php');
    }
}
