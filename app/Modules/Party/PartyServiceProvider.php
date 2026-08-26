<?php

declare(strict_types=1);

namespace App\Modules\Party;

use App\Modules\Party\Domain\Contracts\PartyRepositoryInterface;
use App\Modules\Party\Infrastructure\Persistence\EloquentPartyRepository;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class PartyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PartyRepositoryInterface::class, EloquentPartyRepository::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'party');

        Route::middleware(['web'])
            ->group(__DIR__.'/Presentation/Http/Route/web.php');
    }
}
