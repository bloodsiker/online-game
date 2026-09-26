<?php

declare(strict_types=1);

namespace App\Modules\Influence\Infrastructure\Persistence\Models;

use App\Modules\Share\Domain\Enums\ShareItemStatType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InfluenceMedalStat extends Model
{
    protected $fillable = ['influence_medal_id', 'stat_type', 'value', 'is_percent'];

    protected $casts = ['stat_type' => ShareItemStatType::class, 'value' => 'float', 'is_percent' => 'boolean'];

    public function medal(): BelongsTo
    {
        return $this->belongsTo(InfluenceMedal::class, 'influence_medal_id');
    }
}
