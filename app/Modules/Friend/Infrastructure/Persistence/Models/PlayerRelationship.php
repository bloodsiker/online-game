<?php

declare(strict_types=1);

namespace App\Modules\Friend\Infrastructure\Persistence\Models;

use App\Enums\PlayerRelationshipType;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $player_id
 * @property int $target_id
 * @property PlayerRelationshipType $type
 * @property string|null $status
 * @property-read Player $player
 * @property-read Player $target
 */
class PlayerRelationship extends Model
{
    protected $fillable = ['player_id', 'target_id', 'type', 'status'];

    protected $casts = [
        'type' => PlayerRelationshipType::class,
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id');
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'target_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }
}
