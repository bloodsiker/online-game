<?php

declare(strict_types=1);

namespace App\Modules\Clan;

use App\Modules\Clan\Domain\Contracts\TransactionManager;
use App\Modules\Clan\Domain\Repositories\ClanRepositoryInterface;
use App\Modules\Clan\Infrastructure\Persistence\EloquentClanRepository;
use App\Modules\Clan\Infrastructure\Persistence\LaravelTransactionManager;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ClanServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ClanRepositoryInterface::class, EloquentClanRepository::class);
        $this->app->bind(TransactionManager::class, LaravelTransactionManager::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'clan');

        Route::middleware(['web'])
            ->group(__DIR__.'/Presentation/Http/Route/web.php');
    }
}
