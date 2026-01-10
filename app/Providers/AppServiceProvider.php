<?php

namespace App\Providers;

use App\Models\User;
use App\Services\BackpackService;
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
            return new ShopCartService();
        });

        $this->app->singleton(BackpackService::class, function () {
            return new BackpackService();
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
    }
}
