<?php

declare(strict_types=1);

namespace App\Modules\Player\Infrastructure\Persistence\Models;

use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerSlot extends Model
{
    protected $fillable = [
        'player_id',
        'slot_number',
        'entity_type',
        'entity_id',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'entity_id')
            ->where('entity_type', 'item');
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(PlayerMagicSkill::class, 'entity_id')
            ->where('entity_type', 'skill');
    }
}
