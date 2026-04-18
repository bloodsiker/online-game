<?php

declare(strict_types=1);

namespace App\Modules\Item;

use App\Modules\Item\Domain\Contracts\ItemReadRepository;
use App\Modules\Item\Infrastructure\Persistence\EloquentItemReadRepository;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ItemServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ItemReadRepository::class, EloquentItemReadRepository::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'item');

        Route::middleware(['web'])
            ->group(__DIR__.'/Presentation/Http/Route/web.php');
    }
}
