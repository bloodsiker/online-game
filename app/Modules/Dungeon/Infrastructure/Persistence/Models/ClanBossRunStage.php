<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Infrastructure\Persistence\Models;

use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClanBossRunStage extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_KILLED = 'killed';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_SKIPPED = 'skipped';

    protected $fillable = [
        'clan_boss_run_id', 'dungeon_stage_id', 'monster_on_location_id',
        'hp_max', 'status', 'started_at', 'expires_at', 'killed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'killed_at' => 'datetime',
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(ClanBossRun::class, 'clan_boss_run_id');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(DungeonStage::class, 'dungeon_stage_id');
    }

    public function bossInstance(): BelongsTo
    {
        return $this->belongsTo(MonsterOnLocation::class, 'monster_on_location_id');
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(ClanBossContribution::class, 'dungeon_stage_id', 'dungeon_stage_id')
            ->where('clan_boss_run_id', $this->clan_boss_run_id);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function killDurationSeconds(): ?int
    {
        if ($this->status !== self::STATUS_KILLED || $this->started_at === null || $this->killed_at === null) {
            return null;
        }

        $seconds = $this->started_at->diffInSeconds($this->killed_at, false);

        return $seconds >= 0 ? (int) $seconds : null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && now()->gt($this->expires_at);
    }
}
