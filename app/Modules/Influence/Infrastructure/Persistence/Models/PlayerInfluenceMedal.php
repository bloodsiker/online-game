<?php

declare(strict_types=1);

namespace App\Modules\Influence\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerInfluenceMedal extends Model
{
    protected $fillable = ['user_id', 'influence_medal_id', 'earned_at'];

    protected $casts = ['earned_at' => 'datetime'];

    public function medal(): BelongsTo
    {
        return $this->belongsTo(InfluenceMedal::class, 'influence_medal_id');
    }
}
