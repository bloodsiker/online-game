<?php

declare(strict_types=1);

namespace App\Modules\PlayerTrade;

use App\Modules\PlayerTrade\Application\Listeners\UpdateTradeAfterMovement;
use App\Modules\PlayerTrade\Infrastructure\Persistence\Models\PlayerTradeSession;
use App\Modules\PlayerTrade\Infrastructure\Persistence\Observers\PlayerTradeSessionObserver;
use App\Modules\PlayerTrade\Presentation\ViewComposers\PlayerTradeLinkComposer;
use App\Modules\User\Domain\Events\UserLocationChanged;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class PlayerTradeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        PlayerTradeSession::observe(PlayerTradeSessionObserver::class);
        Event::listen(UserLocationChanged::class, UpdateTradeAfterMovement::class);
        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'player-trade');
        View::composer('location::index', PlayerTradeLinkComposer::class);
        Route::middleware(['web', 'auth', 'prison.context'])->group(__DIR__.'/Presentation/Http/Route/web.php');
    }
}
