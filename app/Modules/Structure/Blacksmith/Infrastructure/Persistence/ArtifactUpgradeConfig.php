<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Infrastructure\Persistence;

use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ArtifactUpgradeConfig extends Model
{
    protected $fillable = ['share_item_id', 'is_enabled', 'gold_cost'];

    protected $casts = ['is_enabled' => 'boolean', 'gold_cost' => 'integer'];

    public function materials(): BelongsToMany
    {
        return $this->belongsToMany(ShareItem::class, 'artifact_upgrade_materials', 'config_id', 'share_item_id')->withPivot('count');
    }
}
