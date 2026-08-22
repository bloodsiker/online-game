<?php

declare(strict_types=1);

namespace App\Modules\Referral;

use App\Modules\Player\Domain\Events\PlayerLeveledUp;
use App\Modules\Referral\Application\Listeners\GrantReferralReward;
use App\Modules\Referral\Domain\Contracts\ReferralRepository;
use App\Modules\Referral\Domain\Contracts\ReferralRewardIssuer;
use App\Modules\Referral\Domain\Contracts\TransactionManager;
use App\Modules\Referral\Infrastructure\Persistence\EloquentReferralRepository;
use App\Modules\Referral\Infrastructure\Persistence\EloquentReferralRewardIssuer;
use App\Modules\Referral\Infrastructure\Persistence\LaravelTransactionManager;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ReferralServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ReferralRepository::class, EloquentReferralRepository::class);
        $this->app->bind(ReferralRewardIssuer::class, EloquentReferralRewardIssuer::class);
        $this->app->bind(TransactionManager::class, LaravelTransactionManager::class);
    }

    public function boot(): void
    {
        Event::listen(PlayerLeveledUp::class, GrantReferralReward::class);

        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'referral');

        Route::middleware(['web'])
            ->group(__DIR__.'/Presentation/Http/Route/web.php');

        Route::middleware(['web', 'isAdmin'])
            ->prefix('admin')
            ->name('admin.')
            ->group(__DIR__.'/Presentation/Http/Route/admin.php');
    }
}
