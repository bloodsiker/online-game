<?php

namespace App\Models\Quest;

use App\Models\ShareItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestReward extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'amount', 'share_item_id'];

    public function quest(): BelongsTo
    {
        return $this->belongsTo(Quest::class,'quest_id');
    }

    public function itemInfo(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'share_item_id');
    }
}
