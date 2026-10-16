<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Infrastructure\Persistence\Models;

use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClanBossPlayerAttackState extends Model
{
    protected $fillable = [
        'clan_boss_run_id', 'user_id', 'hits_in_batch', 'cooldown_until',
    ];

    protected $casts = [
        'hits_in_batch' => 'integer',
        'cooldown_until' => 'datetime',
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(ClanBossRun::class, 'clan_boss_run_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
