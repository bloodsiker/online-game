<?php

namespace App\Models\Quest;

use App\Enums\QuestRewardType;
use App\Models\Location\Location;
use App\Models\Reputation\Reputation;
use App\Models\Share\ShareItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestReward extends Model
{
    use HasFactory;

    protected $casts = ['type' => QuestRewardType::class];

    protected $fillable = ['quest_id', 'type', 'amount', 'share_item_id', 'location_id', 'reputation_id'];

    public function quest(): BelongsTo
    {
        return $this->belongsTo(Quest::class, 'quest_id');
    }

    public function itemInfo(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'share_item_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function reputation(): BelongsTo
    {
        return $this->belongsTo(Reputation::class, 'reputation_id');
    }
}
