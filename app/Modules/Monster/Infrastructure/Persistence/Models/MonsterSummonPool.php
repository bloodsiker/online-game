<?php

declare(strict_types=1);

namespace App\Modules\Monster\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonsterSummonPool extends Model
{
    protected $table = 'monster_summon_pool';

    protected $fillable = [
        'monster_id', 'minion_monster_id', 'weight',
    ];

    protected $casts = [
        'weight' => 'integer',
    ];

    public function monster(): BelongsTo
    {
        return $this->belongsTo(Monster::class, 'monster_id');
    }

    public function minionMonster(): BelongsTo
    {
        return $this->belongsTo(Monster::class, 'minion_monster_id');
    }
}