<?php

declare(strict_types=1);

namespace App\Modules\Weather;

use App\Modules\User\Domain\Events\UserLocationChanged;
use App\Modules\Weather\Application\Listeners\ApplyWeatherOnArrival;
use App\Modules\Weather\Application\Services\WeatherService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

final class WeatherServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(WeatherService::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/Presentation/Views', 'weather');
        Event::listen(UserLocationChanged::class, ApplyWeatherOnArrival::class);
    }
}
