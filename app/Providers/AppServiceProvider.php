<?php

namespace App\Providers;

use App\Models\User;
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
        //
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
