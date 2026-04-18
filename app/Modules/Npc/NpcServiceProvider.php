<?php

declare(strict_types=1);

namespace App\Modules\Npc;

use App\Modules\Npc\Domain\Contracts\NpcReadRepository;
use App\Modules\Npc\Infrastructure\Persistence\EloquentNpcReadRepository;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class NpcServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(NpcReadRepository::class, EloquentNpcReadRepository::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'npc');

        Route::middleware(['web'])
            ->group(__DIR__.'/Presentation/Http/Route/web.php');
    }
}
