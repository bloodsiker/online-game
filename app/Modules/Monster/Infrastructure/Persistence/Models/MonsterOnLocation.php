<?php

declare(strict_types=1);

namespace App\Modules\Monster\Infrastructure\Persistence\Models;

use App\Modules\Dungeon\Infrastructure\Persistence\Models\ClanBossRunStage;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $location_id
 * @property int $monster_id
 * @property int|null $dungeon_session_id
 * @property int|null $world_event_run_id
 * @property int|null $clan_boss_stage_state_id
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
        'location_id', 'dungeon_session_id', 'world_event_run_id', 'clan_boss_stage_state_id',
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
        // Общие инстансы клан-боссов и отмеченные в админке монстры не регенерируют HP.
        if ($this->clan_boss_stage_state_id !== null || $this->monster->does_not_regenerate_hp) {
            return;
        }

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

    public function isClanBoss(): bool
    {
        return $this->clan_boss_stage_state_id !== null;
    }

    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        $clanId = $user?->clanMembership?->clan_id;

        return $query->where(function (Builder $query) use ($clanId): void {
            $query->whereNull('clan_boss_stage_state_id');

            if ($clanId === null) {
                return;
            }

            $query->orWhereHas('clanBossStageState', function (Builder $stageQuery) use ($clanId): void {
                $stageQuery->where('status', ClanBossRunStage::STATUS_ACTIVE)
                    ->where(function (Builder $expiryQuery): void {
                        $expiryQuery->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                    })
                    ->whereHas('run', function (Builder $runQuery) use ($clanId): void {
                        $runQuery->where('clan_id', $clanId)
                            ->where('status', 'active')
                            ->where(function (Builder $expiryQuery): void {
                                $expiryQuery->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                            });
                    });
            });
        });
    }

    public function scopeShownOnLocation(Builder $query): Builder
    {
        // Клан-босс отображается в интерфейсе кланового похода, а не среди
        // обычных монстров общей игровой локации.
        return $query->whereNull('clan_boss_stage_state_id');
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

    public function clanBossStageState(): BelongsTo
    {
        return $this->belongsTo(ClanBossRunStage::class, 'clan_boss_stage_state_id');
    }
}
