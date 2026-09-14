<?php

declare(strict_types=1);

namespace App\Modules\Reputation;

use App\Modules\Reputation\Application\Listeners\RecordEarnedMedal;
use App\Modules\Reputation\Domain\Contracts\ReputationReadRepository;
use App\Modules\Reputation\Domain\Events\ReputationMedalEarned;
use App\Modules\Reputation\Infrastructure\Persistence\EloquentReputationReadRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ReputationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ReputationReadRepository::class, EloquentReputationReadRepository::class);
    }

    public function boot(): void
    {
        Event::listen(ReputationMedalEarned::class, RecordEarnedMedal::class);

        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'reputation');

        Route::middleware(['web'])
            ->group(__DIR__.'/Presentation/Http/Route/web.php');
    }
}
