<?php

declare(strict_types=1);

namespace App\Models\Dungeon;

use App\Enums\DungeonRewardType;
use App\Models\Share\ShareItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int               $id
 * @property int               $dungeon_id
 * @property DungeonRewardType $type
 * @property int|null          $share_item_id
 * @property int               $amount_min
 * @property int               $amount_max
 * @property float             $drop_chance
 * @property int|null          $pity_threshold
 * @property bool              $is_pity_only
 * @property-read Dungeon        $dungeon
 * @property-read ShareItem|null $shareItem
 */
class DungeonReward extends Model
{
    protected $fillable = [
        'dungeon_id', 'type', 'share_item_id',
        'amount_min', 'amount_max', 'drop_chance',
        'pity_threshold', 'is_pity_only',
    ];

    protected $casts = [
        'type'         => DungeonRewardType::class,
        'is_pity_only' => 'boolean',
        'drop_chance'  => 'float',
    ];

    public function dungeon(): BelongsTo
    {
        return $this->belongsTo(Dungeon::class);
    }

    public function shareItem(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'share_item_id');
    }

    public function isItemReward(): bool
    {
        return $this->type === DungeonRewardType::ITEM;
    }

    public function hasPity(): bool
    {
        return $this->pity_threshold !== null;
    }

    public function randomAmount(): int
    {
        return rand($this->amount_min, $this->amount_max);
    }
}