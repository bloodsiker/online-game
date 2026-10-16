<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Infrastructure\Persistence\Models;

use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClanBossContribution extends Model
{
    protected $fillable = [
        'clan_boss_run_id', 'dungeon_stage_id', 'user_id',
        'damage', 'hits', 'last_hit_at', 'rewarded',
    ];

    protected $casts = [
        'last_hit_at' => 'datetime',
        'rewarded' => 'boolean',
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(ClanBossRun::class, 'clan_boss_run_id');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(DungeonStage::class, 'dungeon_stage_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
