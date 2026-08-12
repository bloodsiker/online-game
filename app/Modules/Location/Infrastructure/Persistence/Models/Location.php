<?php

declare(strict_types=1);

namespace App\Modules\Location\Infrastructure\Persistence\Models;

use App\Models\Map;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\Dungeon;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareAction;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'last_respawn_monster_at' => 'datetime',
        ];
    }

    protected function image(): Attribute
    {
        return Attribute::make(get: fn (?string $value) => resolve_storage_image_url($value));
    }

    public function northSide(): BelongsTo
    {
        return $this->belongsTo(self::class, 'north');
    }

    public function southSide(): BelongsTo
    {
        return $this->belongsTo(self::class, 'south');
    }

    public function eastSide(): BelongsTo
    {
        return $this->belongsTo(self::class, 'east');
    }

    public function westSide(): BelongsTo
    {
        return $this->belongsTo(self::class, 'west');
    }

    public function upSide(): BelongsTo
    {
        return $this->belongsTo(self::class, 'up');
    }

    public function downSide(): BelongsTo
    {
        return $this->belongsTo(self::class, 'down');
    }

    public function itemsOnLocation(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'item_on_locations', 'location_id', 'item_id')
            ->with('itemInfo')
            ->withPivot(['count', 'dungeon_session_id']);
    }

    public function map(): BelongsTo
    {
        return $this->belongsTo(Map::class, 'map_id');
    }

    public function monsters(): BelongsToMany
    {
        return $this->belongsToMany(Monster::class, 'location_has_monsters', 'location_id', 'monster_id')->withPivot('aggression');
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

    public function dungeon(): BelongsTo
    {
        return $this->belongsTo(Dungeon::class, 'dungeon_id');
    }
}
