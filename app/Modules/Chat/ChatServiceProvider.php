<?php

declare(strict_types=1);

namespace App\Modules\Chat;

use App\Modules\Chat\Application\Listeners\SendLevelUpSystemMessage;
use App\Modules\Chat\Application\Listeners\SendQuestItemDropMessage;
use App\Modules\Chat\Domain\Repositories\ChatMessageRepositoryInterface;
use App\Modules\Chat\Infrastructure\Persistence\EloquentChatMessageRepository;
use App\Modules\Player\Domain\Events\PlayerLeveledUp;
use App\Modules\Quest\Domain\Events\QuestItemDropped;
use Illuminate\Support\Facades\Event;
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
        Event::listen(PlayerLeveledUp::class, SendLevelUpSystemMessage::class);
        Event::listen(QuestItemDropped::class, SendQuestItemDropMessage::class);

        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'chat');

        Route::middleware(['web'])
            ->group(__DIR__.'/Presentation/Http/Route/web.php');
    }
}
