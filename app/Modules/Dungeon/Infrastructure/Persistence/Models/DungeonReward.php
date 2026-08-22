<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Infrastructure\Persistence\Models;

use App\Modules\Dungeon\Domain\Enums\DungeonRewardType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DungeonReward extends Model
{
    protected $fillable = [
        'dungeon_id', 'type', 'share_item_id',
        'amount_min', 'amount_max', 'drop_chance',
    ];

    protected $casts = [
        'type' => DungeonRewardType::class,
        'drop_chance' => 'float',
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

    public function randomAmount(): int
    {
        return rand($this->amount_min, $this->amount_max);
    }
}
