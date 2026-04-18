<?php

declare(strict_types=1);

namespace App\Modules\Interface;

use App\Modules\Interface\Domain\Contracts\InterfaceReadRepository;
use App\Modules\Interface\Infrastructure\Persistence\EloquentInterfaceReadRepository;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class InterfaceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(InterfaceReadRepository::class, EloquentInterfaceReadRepository::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'interface');

        Route::middleware(['web'])
            ->group(__DIR__.'/Presentation/Http/Route/web.php');
    }
}
