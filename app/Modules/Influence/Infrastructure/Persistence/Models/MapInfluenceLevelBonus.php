<?php

declare(strict_types=1);

namespace App\Modules\Influence\Infrastructure\Persistence\Models;

use App\Modules\Influence\Domain\Enums\InfluenceBonusType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MapInfluenceLevelBonus extends Model
{
    protected $fillable = ['map_influence_level_id', 'bonus_type', 'value'];

    protected $casts = ['bonus_type' => InfluenceBonusType::class, 'value' => 'float'];

    public function level(): BelongsTo
    {
        return $this->belongsTo(MapInfluenceLevel::class, 'map_influence_level_id');
    }
}
