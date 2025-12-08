<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Monster\Monster;
use App\Models\Item\Item;

class Location extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'last_respawn_monster_at' => 'datetime',
        ];
    }

    public function northSide(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'north');
    }

    public function southSide(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'south');
    }

    public function eastSide(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'east');
    }

    public function westSide(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'west');
    }

    public function upSide(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'up');
    }

    public function downSide(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'down');
    }

    public function itemsOnLocation(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'item_on_locations', 'location_id', 'item_id')->with('itemInfo')->withPivot(['count']);
    }

    public function map(): BelongsTo
    {
        return $this->belongsTo(Map::class, 'map_id');
    }

    public function monsters(): BelongsToMany
    {
        return $this->belongsToMany(Monster::class, 'location_has_monsters', 'location_id', 'monster_id');
    }

    public function monstersOnLocation(): BelongsToMany
    {
        return $this->belongsToMany(Monster::class, 'monster_on_locations', 'location_id', 'monster_id')
            ->where('active', 1);
    }

    public function structures(): HasMany
    {
        return $this->hasMany(Structure::class, 'location_id')->with(['actions']);
    }

    public function actions(): BelongsToMany
    {
        return $this->belongsToMany(ShareAction::class, 'location_actions', 'location_id', 'share_action_id');
    }

    public function npcs(): HasMany
    {
        return $this->hasMany(Npc::class, 'location_id');
    }
}
