<?php

declare(strict_types=1);

namespace App\Modules\Premium;

use App\Modules\Premium\Application\Services\PremiumBonusService;
use Illuminate\Support\ServiceProvider;

final class PremiumServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(PremiumBonusService::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'premium');
    }
}
