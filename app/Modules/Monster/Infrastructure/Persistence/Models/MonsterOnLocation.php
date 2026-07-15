<?php

declare(strict_types=1);

namespace App\Modules\Monster\Infrastructure\Persistence\Models;

use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $location_id
 * @property int $monster_id
 * @property int|null $dungeon_session_id
 * @property int $hp_max
 * @property int $hp_now
 * @property int $active
 * @property int $is_drop_money
 * @property int $current_phase
 * @property Carbon|null $last_regen_at
 * @property-read Location $location
 * @property-read Monster $monster
 */
class MonsterOnLocation extends Model
{
    use HasFactory;

    const REGEN_INTERVAL = 5;   // seconds between regen ticks

    const FULL_REGEN_TIME = 1200; // seconds for full HP recovery

    protected $table = 'monster_on_locations';

    protected $fillable = [
        'location_id', 'dungeon_session_id',
        'monster_id', 'hp_max', 'hp_now',
        'active', 'is_drop_money', 'current_phase', 'aggression',
    ];

    protected $casts = [
        'last_regen_at' => 'datetime',
    ];

    protected $attributes = [
        'is_drop_money' => 0,
    ];

    public function regenerate(): void
    {
        if ($this->hp_now >= $this->hp_max) {
            return;
        }

        $now = Carbon::now();

        if (! $this->last_regen_at) {
            $this->last_regen_at = $now;
            $this->save();

            return;
        }

        $seconds = (int) $this->last_regen_at->diffInSeconds($now);
        $ticks = intdiv($seconds, self::REGEN_INTERVAL);

        if ($ticks <= 0) {
            return;
        }

        $totalTicks = self::FULL_REGEN_TIME / self::REGEN_INTERVAL;
        $hpPerTick = $this->hp_max / $totalTicks;

        $this->hp_now = min(
            $this->hp_max,
            (int) floor($this->hp_now + ($hpPerTick * $ticks))
        );

        $this->last_regen_at = $this->last_regen_at->addSeconds($ticks * self::REGEN_INTERVAL);
        $this->save();
    }

    /**
     * Итоговая агрессия инстанса.
     * Если задана локальная — использует её, иначе берёт из базового монстра.
     */
    public function getAggression(): int
    {
        return $this->aggression ?? $this->monster->aggression;
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function monster(): BelongsTo
    {
        return $this->belongsTo(Monster::class, 'monster_id');
    }
}
