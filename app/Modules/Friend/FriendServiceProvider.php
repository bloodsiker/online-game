<?php

declare(strict_types=1);

namespace App\Modules\Friend;

use App\Modules\Friend\Domain\Contracts\FriendRelationshipRepository;
use App\Modules\Friend\Infrastructure\Persistence\EloquentFriendRelationshipRepository;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class FriendServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(FriendRelationshipRepository::class, EloquentFriendRelationshipRepository::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'friend');

        Route::middleware(['web'])
            ->group(__DIR__.'/Presentation/Http/Route/web.php');
    }
}
