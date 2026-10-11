<?php

declare(strict_types=1);

namespace App\Modules\Auth;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'auth');

        Route::middleware(['web'])
            ->group(__DIR__.'/Presentation/Http/Route/web.php');
    }
}
