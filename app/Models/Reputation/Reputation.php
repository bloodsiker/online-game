<?php

declare(strict_types=1);

namespace App\Models\Reputation;

use App\Models\Npc;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    public function playerReputations(): HasMany
    {
        return $this->hasMany(PlayerReputation::class, 'reputation_id');
    }
}
