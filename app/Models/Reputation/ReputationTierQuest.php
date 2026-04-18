<?php

declare(strict_types=1);

namespace App\Models\Reputation;

use App\Modules\Quest\Infrastructure\Persistence\Models\Quest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReputationTierQuest extends Model
{
    protected $table = 'reputation_tier_quests';

    protected $fillable = ['tier_id', 'quest_id'];

    public function tier(): BelongsTo
    {
        return $this->belongsTo(ReputationTier::class, 'tier_id');
    }

    public function quest(): BelongsTo
    {
        return $this->belongsTo(Quest::class, 'quest_id');
    }
}
