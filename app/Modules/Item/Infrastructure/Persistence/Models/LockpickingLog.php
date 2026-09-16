<?php

declare(strict_types=1);

namespace App\Modules\Item\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class LockpickingLog extends Model
{
    protected $fillable = [
        'player_id',
        'item_id',
        'share_item_id',
        'lockpick_share_item_id',
        'source',
        'result',
        'skill_level',
        'lockpick_tier',
        'success_chance',
        'trap_triggered',
        'lockpick_broken',
        'trap_avoided',
    ];

    protected $casts = [
        'success_chance' => 'float',
        'trap_triggered' => 'boolean',
        'lockpick_broken' => 'boolean',
        'trap_avoided' => 'boolean',
    ];
}
