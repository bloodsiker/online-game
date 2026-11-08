<?php

declare(strict_types=1);

namespace App\Modules\Telegram;

use App\Modules\Interface\Domain\Contracts\OnlinePlayersReader;
use App\Modules\Interface\Infrastructure\Persistence\EloquentOnlinePlayersReader;
use App\Modules\News\Domain\Events\LaunchSubscribed;
use App\Modules\Structure\Auction\Domain\Contracts\AuctionListingsReader;
use App\Modules\Structure\Auction\Domain\Contracts\OwnMarketListingsReader;
use App\Modules\Structure\Auction\Infrastructure\Persistence\EloquentAuctionListingsReader;
use App\Modules\Structure\Auction\Infrastructure\Persistence\EloquentOwnMarketListingsReader;
use App\Modules\Telegram\Application\Listeners\NotifyAdminAboutLaunchSubscription;
use App\Modules\Telegram\Application\Listeners\NotifyAdminAboutRegistration;
use App\Modules\Telegram\Domain\Contracts\BackpackPreviewRenderer;
use App\Modules\Telegram\Domain\Contracts\TelegramCallbackResponder;
use App\Modules\Telegram\Domain\Contracts\TelegramLinkRepository;
use App\Modules\Telegram\Domain\Contracts\TelegramUpdateRepository;
use App\Modules\Telegram\Infrastructure\Images\GdBackpackPreviewRenderer;
use App\Modules\Telegram\Infrastructure\Persistence\DatabaseTelegramLinkRepository;
use App\Modules\Telegram\Infrastructure\Persistence\DatabaseTelegramUpdateRepository;
use App\Modules\Telegram\Infrastructure\TelegramCallbackAcknowledger;
use App\Modules\Telegram\Presentation\Console\ConfigureTelegramWebhook;
use App\Modules\Telegram\Presentation\Console\PruneTelegramUpdates;
use App\Modules\User\Domain\Contracts\CharacterDetailsReader;
use App\Modules\User\Domain\Contracts\CharacterSummaryReader;
use App\Modules\User\Domain\Events\UserRegistered;
use App\Modules\User\Infrastructure\Persistence\DatabaseCharacterSummaryReader;
use App\Modules\User\Infrastructure\Persistence\EloquentCharacterDetailsReader;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class TelegramServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TelegramLinkRepository::class, DatabaseTelegramLinkRepository::class);
        $this->app->bind(TelegramUpdateRepository::class, DatabaseTelegramUpdateRepository::class);
        $this->app->bind(BackpackPreviewRenderer::class, GdBackpackPreviewRenderer::class);
        $this->app->bind(TelegramCallbackResponder::class, TelegramCallbackAcknowledger::class);
        $this->app->bind(CharacterDetailsReader::class, EloquentCharacterDetailsReader::class);
        $this->app->bind(OnlinePlayersReader::class, EloquentOnlinePlayersReader::class);
        $this->app->bind(CharacterSummaryReader::class, DatabaseCharacterSummaryReader::class);
        $this->app->bind(OwnMarketListingsReader::class, EloquentOwnMarketListingsReader::class);
        $this->app->bind(AuctionListingsReader::class, EloquentAuctionListingsReader::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'telegram');
        Event::listen(UserRegistered::class, NotifyAdminAboutRegistration::class);
        Event::listen(LaunchSubscribed::class, NotifyAdminAboutLaunchSubscription::class);
        Route::middleware(['web', 'auth'])->group(__DIR__.'/Presentation/Http/Route/web.php');
        // No web/session middleware: these endpoints authenticate the bot, not a browser session.
        $this->loadRoutesFrom(__DIR__.'/Presentation/Http/Route/api.php');
        RateLimiter::for('telegram-api', static fn (Request $request): Limit => Limit::perMinute(60)
            ->by((string) $request->attributes->get('telegram.account')->telegramUserId));
        RateLimiter::for('telegram-mini-app', static fn (Request $request): Limit => Limit::perMinute(60)
            ->by((string) $request->attributes->get('telegram.account')->telegramUserId));
        foreach (['read' => 120, 'send' => 20, 'auth' => 60] as $action => $limit) {
            RateLimiter::for('telegram-chat-'.$action, static fn (Request $request): Limit => Limit::perMinute($limit)
                ->by((string) $request->attributes->get('telegram.account')->userId));
        }
        RateLimiter::for('telegram-webhook', static function (Request $request): Limit {
            $id = $request->input('callback_query.from.id', $request->input('message.from.id'));

            return Limit::perMinute(120)->by(is_scalar($id) ? (string) $id : (string) $request->ip());
        });
        if ($this->app->runningInConsole()) {
            $this->commands([ConfigureTelegramWebhook::class, PruneTelegramUpdates::class]);
            $this->callAfterResolving(Schedule::class, static function (Schedule $schedule): void {
                $schedule->command('telegram:prune')->daily()->withoutOverlapping();
            });
        }
    }
}
