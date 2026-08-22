<?php

declare(strict_types=1);

namespace App\Modules\Quest\Infrastructure\Persistence\Models;

use App\Models\Map;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestObjective extends Model
{
    use HasFactory;

    protected $casts = [
        'drop_chance' => 'float',
        'target_ids' => 'array',
    ];

    protected $fillable = [
        'quest_id', 'stage_id', 'type', 'target_type', 'target_id', 'target_ids',
        'share_item_id', 'map_id', 'required_amount', 'drop_chance', 'description',
    ];

    /**
     * true — цель задания это ЛЮБОЙ из нескольких мобов (напр. несколько уровневых
     * вариантов одного «семейного» имени: Костяной Часовой 35/36 ур.) — прогресс
     * засчитывается суммарно по всем ним как за одну цель, а не по каждому отдельно.
     */
    public function matchesMonster(int $monsterId): bool
    {
        if (! empty($this->target_ids)) {
            return in_array($monsterId, $this->target_ids, true);
        }

        return (int) $this->target_id === $monsterId;
    }

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

    public function shareItem(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'target_id');
    }

    public function collectItem(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'share_item_id');
    }
}
