<?php

declare(strict_types=1);

namespace App\Modules\Player\Infrastructure\Persistence\Models;

use App\Modules\Player\Domain\Enums\InjuryBodyPart;
use App\Modules\Player\Domain\Enums\InjurySeverity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property InjuryBodyPart $body_part
 * @property InjurySeverity $severity
 * @property string|null $image
 * @property int $duration_seconds
 * @property int $drop_weight
 * @property array|null $stat_modifiers
 * @property bool $is_active
 */
class InjuryType extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'body_part',
        'severity',
        'image',
        'duration_seconds',
        'drop_weight',
        'stat_modifiers',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'body_part' => InjuryBodyPart::class,
            'severity' => InjurySeverity::class,
            'duration_seconds' => 'integer',
            'drop_weight' => 'integer',
            'stat_modifiers' => 'array',
            'is_active' => 'boolean',
        ];
    }

    protected function image(): Attribute
    {
        return Attribute::make(get: fn (?string $value): ?string => resolve_storage_image_url($value));
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('drop_weight', '>', 0);
    }

    public function playerInjuries(): HasMany
    {
        return $this->hasMany(PlayerInjury::class);
    }

    public function durationMinutes(): int
    {
        return max(1, (int) ceil($this->duration_seconds / 60));
    }
}
