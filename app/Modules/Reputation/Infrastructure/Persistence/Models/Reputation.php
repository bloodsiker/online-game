<?php

declare(strict_types=1);

namespace App\Modules\Reputation\Infrastructure\Persistence\Models;

use App\Modules\Effect\Infrastructure\Persistence\Models\Effect;
use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;
use App\Modules\Reputation\Domain\Enums\DivineFavorType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareStructureCategory;
use App\Modules\Structure\ReputationExchange\Infrastructure\Persistence\Models\ReputationExchange;
use App\Modules\Structure\ReputationExchange\Infrastructure\Persistence\Models\ReputationGambleOption;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reputation extends Model
{
    protected $table = 'reputations';

    protected $fillable = [
        'name', 'description', 'npc_id', 'icon',
        'favor_effect_type', 'favor_proc_chance', 'elixir_share_item_id', 'favor_marker_effect_id',
    ];

    protected $casts = [
        'favor_effect_type' => DivineFavorType::class,
        'favor_proc_chance' => 'integer',
    ];

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

    public function gambleOptions(): HasMany
    {
        return $this->hasMany(ReputationGambleOption::class, 'reputation_id')->orderBy('share_item_id')->orderBy('sort_order');
    }

    public function exchangeItems(): HasMany
    {
        return $this->hasMany(ReputationExchange::class, 'reputation_id')->orderBy('sort_order');
    }

    public function elixirItem(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'elixir_share_item_id');
    }

    public function favorMarkerEffect(): BelongsTo
    {
        return $this->belongsTo(Effect::class, 'favor_marker_effect_id');
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
