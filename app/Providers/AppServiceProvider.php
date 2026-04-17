<?php

namespace App\Providers;

use App\Modules\User\Infrastructure\Persistence\Models\User;
use App\Services\ShopCartService;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ShopCartService::class, function () {
            return new ShopCartService;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['main.index', 'auth.register'], function ($view) {
            $tenMinutesAgo = Carbon::now()->subMinutes(10);
            $onlineCount = User::where('last_online_at', '>=', $tenMinutesAgo)->count();
            $view->with('onlineCount', $onlineCount);
        });

        View::composer('*', function ($view) {
            if (! auth()->check()) {
                $view->with('playerStatsScript', '');

                return;
            }

            $player = auth()->user()->player;
            if (! $player) {
                $view->with('playerStatsScript', '');

                return;
            }

            $player->loadMissing('skills');

            $view->with('playerStatsScript', sprintf(
                '<script>window.playerStats=%s;</script>',
                json_encode([
                    'lvl' => $player->lvl,
                    'strength' => (int) floor($player->strength),
                    'agility' => (int) floor($player->agility),
                    'intuition' => (int) floor($player->intuition),
                    'wisdom' => (int) floor($player->wisdom),
                    'intelligence' => (int) floor($player->intelligence),
                    'skills' => $player->skills->pluck('lvl', 'skill_id'),
                ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG)
            ));
        });
    }
}
