<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Infrastructure\Persistence\Models;

use App\Modules\Clan\Domain\Models\Clan;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClanBossRun extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'dungeon_id', 'clan_id', 'opened_by_user_id', 'current_stage_id',
        'status', 'started_at', 'expires_at', 'finished_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function dungeon(): BelongsTo
    {
        return $this->belongsTo(Dungeon::class);
    }

    public function clan(): BelongsTo
    {
        return $this->belongsTo(Clan::class);
    }

    public function opener(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by_user_id');
    }

    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(DungeonStage::class, 'current_stage_id');
    }

    public function stageStates(): HasMany
    {
        return $this->hasMany(ClanBossRunStage::class, 'clan_boss_run_id');
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(ClanBossContribution::class, 'clan_boss_run_id');
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && now()->gt($this->expires_at);
    }
}
