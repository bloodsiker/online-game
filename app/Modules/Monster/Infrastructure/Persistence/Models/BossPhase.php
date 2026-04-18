<?php

declare(strict_types=1);

namespace App\Modules\Monster\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BossPhase extends Model
{
    protected $fillable = [
        'monster_id', 'phase_number', 'hp_threshold',
        'stats_modifiers', 'new_skills', 'removed_skills',
        'description',
    ];

    protected $casts = [
        'stats_modifiers' => 'array',
        'new_skills' => 'array',
        'removed_skills' => 'array',
    ];

    public function monster(): BelongsTo
    {
        return $this->belongsTo(Monster::class);
    }
}
