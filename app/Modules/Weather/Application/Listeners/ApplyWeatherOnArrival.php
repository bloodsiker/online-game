<?php

declare(strict_types=1);

namespace App\Modules\Weather\Application\Listeners;

use App\Modules\User\Domain\Events\UserLocationChanged;
use App\Modules\Weather\Application\Services\WeatherService;

final readonly class ApplyWeatherOnArrival
{
    public function __construct(private WeatherService $weather) {}

    public function handle(UserLocationChanged $event): void
    {
        // Погода — приятное дополнение: её сбой не должен ломать переход между локациями.
        try {
            $this->weather->applyOnArrival($event->userId);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
