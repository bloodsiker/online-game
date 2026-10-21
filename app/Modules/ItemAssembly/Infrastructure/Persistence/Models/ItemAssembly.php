<?php

declare(strict_types=1);

namespace App\Modules\ItemAssembly\Infrastructure\Persistence\Models;

use App\Modules\ItemAssembly\Domain\Enums\AssemblyResultMode;
use App\Modules\ItemAssembly\Domain\Enums\AssemblyType;
use App\Modules\Share\Infrastructure\Localization\HasLegacyTranslations;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ItemAssembly extends Model
{
    use HasLegacyTranslations;

    public $translatable = ['name'];

    protected $guarded = ['id', 'legacy_recipe_id', 'legacy_recipe_snapshot'];

    protected $casts = [
        'type' => AssemblyType::class,
        'result_mode' => AssemblyResultMode::class,
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'legacy_recipe_snapshot' => 'array',
    ];

    public function components(): BelongsToMany
    {
        return $this->belongsToMany(ShareItem::class, 'item_assembly_components')->withPivot('count')->orderByPivot('id');
    }

    public function rewards(): BelongsToMany
    {
        return $this->belongsToMany(ShareItem::class, 'item_assembly_rewards')->withPivot(['count', 'weight'])->orderByPivot('id');
    }
}
