<?php

declare(strict_types=1);

namespace App\Modules\Battle;

use App\Modules\Battle\Domain\Contracts\RandomizerInterface;
use App\Modules\Battle\Infrastructure\MtRandomizer;
use App\Modules\Battle\Presentation\Console\SimulateBattleTriangle;
use App\Modules\Battle\Presentation\Console\SimulatePveEncounter;
use App\Modules\Battle\Presentation\Console\SimulatePveRoster;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class BattleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(RandomizerInterface::class, MtRandomizer::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'battle');

        Route::middleware(['web'])
            ->group(__DIR__.'/Presentation/Http/Route/web.php');

        if ($this->app->runningInConsole()) {
            $this->commands([SimulateBattleTriangle::class, SimulatePveEncounter::class, SimulatePveRoster::class]);
        }
    }
}
