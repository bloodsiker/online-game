<?php

declare(strict_types=1);

namespace App\Modules\Chat;

use App\Modules\Chat\Domain\Repositories\ChatMessageRepositoryInterface;
use App\Modules\Chat\Infrastructure\Persistence\EloquentChatMessageRepository;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ChatServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ChatMessageRepositoryInterface::class,
            EloquentChatMessageRepository::class,
        );
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/Presentation/Views', 'chat');

        Route::middleware(['web'])
            ->group(__DIR__ . '/Presentation/Http/Route/web.php');
    }
}