<?php

declare(strict_types=1);

namespace App\Modules\Reputation\Infrastructure\Persistence\Models;

use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareStructureCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reputation extends Model
{
    protected $table = 'reputations';

    protected $fillable = ['name', 'description', 'npc_id', 'icon'];

    public function npc(): BelongsTo
    {
        return $this->belongsTo(Npc::class, 'npc_id');
    }

    public function tiers(): HasMany
    {
        return $this->hasMany(ReputationTier::class, 'reputation_id')->orderBy('min_points');
    }

    public function shopItems(): HasMany
    {
        return $this->hasMany(ReputationShopItem::class, 'reputation_id')->orderBy('sort_order');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            ShareStructureCategory::class,
            'reputation_categories',
            'reputation_id',
            'share_structure_category_id',
        );
    }

    public function playerReputations(): HasMany
    {
        return $this->hasMany(PlayerReputation::class, 'reputation_id');
    }
}
