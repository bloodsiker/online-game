<?php

namespace App\Models\Quest;

use App\Models\Map;
use App\Models\Share\ShareItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestObjective extends Model
{
    use HasFactory;

    protected $casts = [
        'drop_chance' => 'float',
    ];

    protected $fillable = [
        'quest_id',
        'stage_id',
        'type',
        'target_type',
        'target_id',
        'share_item_id',
        'map_id',
        'required_amount',
        'drop_chance',
        'description',
    ];

    public function quest(): BelongsTo
    {
        return $this->belongsTo(Quest::class, 'quest_id');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(QuestStage::class, 'stage_id');
    }

    public function map(): BelongsTo
    {
        return $this->belongsTo(Map::class, 'map_id');
    }

    /** For deliver type: item identified by target_id */
    public function shareItem(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'target_id');
    }

    /** For collect type: the physical item that drops into the backpack */
    public function collectItem(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'share_item_id');
    }
}
