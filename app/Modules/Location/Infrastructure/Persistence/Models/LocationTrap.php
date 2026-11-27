<?php

declare(strict_types=1);

namespace App\Modules\Location\Infrastructure\Persistence\Models;

use App\Modules\Share\Infrastructure\Localization\HasLegacyTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $location_id
 * @property string|null $direction
 * @property string $type
 * @property array<string, mixed>|null $config
 * @property bool $is_active
 */
class LocationTrap extends Model
{
    use HasLegacyTranslations;

    public $translatable = ['name'];

    protected $fillable = ['location_id', 'direction', 'type', 'name', 'config', 'is_active'];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }
}
