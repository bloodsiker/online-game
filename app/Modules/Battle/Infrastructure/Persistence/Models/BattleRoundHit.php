<?php

declare(strict_types=1);

namespace App\Modules\Battle\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $battle_round_id
 * @property string $participant_type user|monster
 * @property int $participant_id user_id или location_monster_id
 * @property int|null $hp_after
 * @property string $action
 */
class BattleRoundHit extends Model
{
    protected $fillable = [
        'battle_round_id',
        'participant_type',
        'participant_id',
        'hp_after',
        'action',
    ];

    public function round(): BelongsTo
    {
        return $this->belongsTo(BattleRound::class, 'battle_round_id');
    }
}
