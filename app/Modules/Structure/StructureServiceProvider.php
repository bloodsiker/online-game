<?php

declare(strict_types=1);

namespace App\Modules\Structure;

use App\Modules\Structure\Bank\Domain\Contracts\BankLogRepository;
use App\Modules\Structure\Bank\Domain\Contracts\BankUserRepository;
use App\Modules\Structure\Bank\Infrastructure\Persistence\EloquentBankLogRepository;
use App\Modules\Structure\Bank\Infrastructure\Persistence\EloquentBankUserRepository;
use App\Modules\Structure\Bank\Infrastructure\Persistence\LaravelTransactionManager as BankLaravelTransactionManager;
use App\Modules\Structure\Blacksmith\Domain\Contracts\BlacksmithInventoryRepository;
use App\Modules\Structure\Blacksmith\Domain\Contracts\BlacksmithReadRepository;
use App\Modules\Structure\Blacksmith\Domain\Contracts\TransactionManager;
use App\Modules\Structure\Blacksmith\Infrastructure\Persistence\EloquentBlacksmithInventoryRepository;
use App\Modules\Structure\Blacksmith\Infrastructure\Persistence\EloquentBlacksmithReadRepository;
use App\Modules\Structure\Blacksmith\Infrastructure\Persistence\LaravelTransactionManager;
use App\Modules\Structure\Exchange\Domain\Contracts\ExchangeInventoryRepository;
use App\Modules\Structure\Exchange\Domain\Contracts\ExchangeReadRepository;
use App\Modules\Structure\Exchange\Domain\Contracts\TransactionManager as ExchangeTransactionManager;
use App\Modules\Structure\Exchange\Infrastructure\Persistence\EloquentExchangeInventoryRepository;
use App\Modules\Structure\Exchange\Infrastructure\Persistence\EloquentExchangeReadRepository;
use App\Modules\Structure\Exchange\Infrastructure\Persistence\LaravelTransactionManager as ExchangeLaravelTransactionManager;
use App\Modules\Structure\Shop\Domain\Contracts\ShopInventoryRepository;
use App\Modules\Structure\Shop\Domain\Contracts\ShopReadRepository;
use App\Modules\Structure\Shop\Domain\Contracts\TransactionManager as ShopTransactionManager;
use App\Modules\Structure\Shop\Infrastructure\Persistence\EloquentShopInventoryRepository;
use App\Modules\Structure\Shop\Infrastructure\Persistence\EloquentShopReadRepository;
use App\Modules\Structure\Shop\Infrastructure\Persistence\LaravelTransactionManager as ShopLaravelTransactionManager;
use App\Modules\Structure\Warehouse\Domain\Contracts\TransactionManager as WarehouseTransactionManager;
use App\Modules\Structure\Warehouse\Domain\Contracts\WarehouseInventoryRepository;
use App\Modules\Structure\Warehouse\Infrastructure\Persistence\EloquentWarehouseInventoryRepository;
use App\Modules\Structure\Warehouse\Infrastructure\Persistence\LaravelTransactionManager as WarehouseLaravelTransactionManager;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class StructureServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BankUserRepository::class, EloquentBankUserRepository::class);
        $this->app->bind(BankLogRepository::class, EloquentBankLogRepository::class);
        $this->app->bind(\App\Modules\Structure\Bank\Domain\Contracts\TransactionManager::class, BankLaravelTransactionManager::class);

        $this->app->bind(BlacksmithReadRepository::class, EloquentBlacksmithReadRepository::class);
        $this->app->bind(BlacksmithInventoryRepository::class, EloquentBlacksmithInventoryRepository::class);
        $this->app->bind(TransactionManager::class, LaravelTransactionManager::class);

        $this->app->bind(ExchangeReadRepository::class, EloquentExchangeReadRepository::class);
        $this->app->bind(ExchangeInventoryRepository::class, EloquentExchangeInventoryRepository::class);
        $this->app->bind(ExchangeTransactionManager::class, ExchangeLaravelTransactionManager::class);

        $this->app->bind(ShopReadRepository::class, EloquentShopReadRepository::class);
        $this->app->bind(ShopInventoryRepository::class, EloquentShopInventoryRepository::class);
        $this->app->bind(ShopTransactionManager::class, ShopLaravelTransactionManager::class);

        $this->app->bind(WarehouseInventoryRepository::class, EloquentWarehouseInventoryRepository::class);
        $this->app->bind(WarehouseTransactionManager::class, WarehouseLaravelTransactionManager::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/Auction/Presentation/Views', 'auction');

        $this->loadViewsFrom(__DIR__.'/Bank/Presentation/Views', 'bank');

        $this->loadViewsFrom(__DIR__.'/PremiumShop/Presentation/Views', 'premium_shop');

        $this->loadViewsFrom(__DIR__.'/Shop/Presentation/Views', 'shop');

        $this->loadViewsFrom(__DIR__.'/Exchange/Presentation/Views', 'exchange');

        $this->loadViewsFrom(__DIR__.'/Blacksmith/Presentation/Views', 'blacksmith');

        $this->loadViewsFrom(__DIR__.'/Warehouse/Presentation/Views', 'warehouse');

        // Auction routes — inside updateLastOnline middleware (same as web.php group)
        Route::middleware(['web', 'updateLastOnline'])
            ->group(__DIR__.'/Auction/Presentation/Http/Route/web.php');

        // Bank routes — only web middleware
        Route::middleware(['web'])
            ->group(__DIR__.'/Bank/Presentation/Http/Route/web.php');

        // PremiumShop routes — inside updateLastOnline middleware
        Route::middleware(['web', 'updateLastOnline'])
            ->group(__DIR__.'/PremiumShop/Presentation/Http/Route/web.php');

        // Shop routes — inside updateLastOnline middleware
        Route::middleware(['web', 'updateLastOnline'])
            ->group(__DIR__.'/Shop/Presentation/Http/Route/web.php');

        // Exchange routes — inside updateLastOnline middleware
        Route::middleware(['web', 'updateLastOnline'])
            ->group(__DIR__.'/Exchange/Presentation/Http/Route/web.php');

        // Blacksmith routes — inside updateLastOnline middleware
        Route::middleware(['web', 'updateLastOnline'])
            ->group(__DIR__.'/Blacksmith/Presentation/Http/Route/web.php');

        // Warehouse routes — inside updateLastOnline middleware
        Route::middleware(['web', 'updateLastOnline'])
            ->group(__DIR__.'/Warehouse/Presentation/Http/Route/web.php');
    }
}
