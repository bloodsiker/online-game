<?php

declare(strict_types=1);

namespace App\Modules\Backpack;

use App\Modules\Backpack\Application\Listeners\GiveStarterBonus;
use App\Modules\Backpack\Domain\Repositories\BackpackReadRepository;
use App\Modules\Backpack\Domain\Repositories\BackpackRepositoryInterface;
use App\Modules\Backpack\Infrastructure\Persistence\EloquentBackpackReadRepository;
use App\Modules\Backpack\Infrastructure\Persistence\EloquentBackpackRepository;
use App\Modules\Backpack\Presentation\Console\AuditInventoryDuplicates;
use App\Modules\User\Domain\Events\UserRegistered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class BackpackServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BackpackReadRepository::class, EloquentBackpackReadRepository::class);
        $this->app->bind(
            BackpackRepositoryInterface::class,
            EloquentBackpackRepository::class,
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([AuditInventoryDuplicates::class]);
        }
        Event::listen(UserRegistered::class, GiveStarterBonus::class);

        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'backpack');

        Route::middleware(['web'])
            ->group(__DIR__.'/Presentation/Http/Route/web.php');
    }
}
