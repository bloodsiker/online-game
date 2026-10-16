<?php

declare(strict_types=1);

namespace App\Modules\Monster\Infrastructure\Persistence\Models;

use App\Modules\Share\Infrastructure\Localization\HasLegacyTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BossPhase extends Model
{
    use HasLegacyTranslations;

    public $translatable = ['description'];

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
