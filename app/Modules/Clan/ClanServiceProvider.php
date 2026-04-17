<?php

declare(strict_types=1);

namespace App\Modules\Clan;

use App\Modules\Clan\Domain\Repositories\ClanRepositoryInterface;
use App\Modules\Clan\Infrastructure\Persistence\EloquentClanRepository;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ClanServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ClanRepositoryInterface::class, EloquentClanRepository::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'clan');

        Route::middleware(['web', 'updateLastOnline'])
            ->group(__DIR__.'/Presentation/Http/Route/web.php');
    }
}
