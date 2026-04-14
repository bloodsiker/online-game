<?php

declare(strict_types=1);

namespace App\Modules\Structure;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class StructureServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/Auction/Presentation/Views', 'auction');

        $this->loadViewsFrom(__DIR__ . '/Bank/Presentation/Views', 'bank');

        $this->loadViewsFrom(__DIR__ . '/PremiumShop/Presentation/Views', 'premium_shop');

        $this->loadViewsFrom(__DIR__ . '/Shop/Presentation/Views', 'shop',);

        $this->loadViewsFrom(__DIR__ . '/Exchange/Presentation/Views', 'exchange',);

        $this->loadViewsFrom(__DIR__ . '/Blacksmith/Presentation/Views', 'blacksmith');

        // Auction routes — inside updateLastOnline middleware (same as web.php group)
        Route::middleware(['web', 'updateLastOnline'])
            ->group(__DIR__ . '/Auction/Presentation/Http/Route/web.php');

        // Bank routes — only web middleware
        Route::middleware(['web'])
            ->group(__DIR__ . '/Bank/Presentation/Http/Route/web.php');

        // PremiumShop routes — inside updateLastOnline middleware
        Route::middleware(['web', 'updateLastOnline'])
            ->group(__DIR__ . '/PremiumShop/Presentation/Http/Route/web.php');

        // Shop routes — inside updateLastOnline middleware
        Route::middleware(['web', 'updateLastOnline'])
            ->group(__DIR__ . '/Shop/Presentation/Http/Route/web.php');

        // Exchange routes — inside updateLastOnline middleware
        Route::middleware(['web', 'updateLastOnline'])
            ->group(__DIR__ . '/Exchange/Presentation/Http/Route/web.php');

        // Blacksmith routes — inside updateLastOnline middleware
        Route::middleware(['web', 'updateLastOnline'])
            ->group(__DIR__ . '/Blacksmith/Presentation/Http/Route/web.php');
    }
}
