<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Infrastructure\Persistence\Models;

use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ClanBossRewardReceipt extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['boss_name' => 'array', 'rewards' => 'array', 'notified_at' => 'datetime', 'user_id' => 'integer'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function stageState(): BelongsTo
    {
        return $this->belongsTo(ClanBossRunStage::class, 'clan_boss_run_stage_id');
    }

    public function bossName(): string
    {
        return ($this->boss_name[app()->getLocale()] ?? '') ?: ($this->boss_name['ru'] ?? '—');
    }
}
