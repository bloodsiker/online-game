<?php

declare(strict_types=1);

namespace App\Modules\Interface;

use App\Modules\Interface\Application\Listeners\BroadcastOnlineCountFromSocket;
use App\Modules\Interface\Application\Listeners\UpdatePlayerPresenceFromSocket;
use App\Modules\Interface\Domain\Contracts\InterfaceReadRepository;
use App\Modules\Interface\Infrastructure\Persistence\EloquentInterfaceReadRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Reverb\Events\ChannelRemoved;
use Laravel\Reverb\Events\MessageReceived;

class InterfaceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(InterfaceReadRepository::class, EloquentInterfaceReadRepository::class);
    }

    public function boot(): void
    {
        Event::listen(MessageReceived::class, UpdatePlayerPresenceFromSocket::class);
        Event::listen(MessageReceived::class, BroadcastOnlineCountFromSocket::class);
        Event::listen(ChannelRemoved::class, BroadcastOnlineCountFromSocket::class);

        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'interface');

        Route::middleware(['web'])
            ->group(__DIR__.'/Presentation/Http/Route/web.php');
    }
}
