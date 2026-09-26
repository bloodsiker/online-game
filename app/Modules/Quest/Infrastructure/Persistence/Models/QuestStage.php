<?php

declare(strict_types=1);

namespace App\Modules\Quest\Infrastructure\Persistence\Models;

use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestStage extends Model
{
    protected $fillable = [
        'quest_id', 'complete_npc_id', 'order', 'title', 'description',
        'stage_type', 'wait_duration_seconds', 'waiting_text', 'ready_text',
        'grant_share_item_id', 'grant_amount',
    ];

    protected $casts = [
        'wait_duration_seconds' => 'integer',
        'grant_amount' => 'integer',
    ];

    public function quest(): BelongsTo
    {
        return $this->belongsTo(Quest::class, 'quest_id');
    }

    public function completeNpc(): BelongsTo
    {
        return $this->belongsTo(Npc::class, 'complete_npc_id');
    }

    public function objectives(): HasMany
    {
        return $this->hasMany(QuestObjective::class, 'stage_id');
    }

    /**
     * Предмет, который NPC физически вручает игроку в рюкзак при завершении этого
     * этапа (не путать с deliver-целями — там наоборот, игрок отдаёт предмет NPC).
     */
    public function grantItem(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'grant_share_item_id');
    }

    public function isWaiting(): bool
    {
        return $this->stage_type === 'wait';
    }
}
