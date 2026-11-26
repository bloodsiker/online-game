<?php

declare(strict_types=1);

namespace App\Modules\Weather\Infrastructure\Persistence\Models;

use App\Modules\Weather\Domain\Enums\WeatherType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $map_id
 * @property WeatherType|null $weather
 * @property Carbon|null $started_at
 * @property Carbon $ends_at
 * @property WeatherType|null $previous_weather
 */
final class MapWeather extends Model
{
    protected $table = 'map_weather';

    protected $fillable = ['map_id', 'weather', 'previous_weather', 'started_at', 'ends_at'];

    protected function casts(): array
    {
        return [
            'weather' => WeatherType::class,
            'previous_weather' => WeatherType::class,
            'started_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function isActive(): bool
    {
        return $this->weather !== null && $this->ends_at->isFuture();
    }
}
