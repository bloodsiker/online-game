<?php

declare(strict_types=1);

namespace App\Modules\Event;

use App\Modules\Event\Application\UseCases\GetLocationWorldEvents;
use App\Modules\Event\Infrastructure\Persistence\Models\WorldEventRun;
use App\Modules\Event\Infrastructure\Persistence\Observers\WorldEventRunObserver;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        WorldEventRun::observe(WorldEventRunObserver::class);

        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'event');
        View::composer(['location::index', 'location::take_items', 'item::pickup_items'], function ($view): void {
            $view->with('locationEvents', auth()->check()
                ? app(GetLocationWorldEvents::class)->execute(auth()->user())
                : []);
        });

        Route::middleware(['web'])
            ->group(__DIR__.'/Presentation/Http/Route/web.php');

        Route::middleware(['web', 'isAdmin'])
            ->prefix('admin')
            ->name('admin.')
            ->group(__DIR__.'/Presentation/Http/Route/admin.php');
    }
}
