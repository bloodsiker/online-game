<?php

namespace App\Models\Monster;

use App\Models\Location;
use App\Models\MagicSkill\Effect;
use App\Models\ShareItem;
use App\Services\Combat\FightHitInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Monster extends Model implements FightHitInterface
{
    use HasFactory;

    protected $fillable = [
        'lvl', 'name', 'hp', 'armor', 'dodge', 'critical', 'min_dmg', 'max_dmg', 'aggression', 'exp', 'min_money', 'max_money'
    ];

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'location_has_monsters', 'monster_id', 'location_id');
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(ShareItem::class, 'monster_has_items', 'monster_id', 'share_item_id')
            ->withPivot('drop_chance', 'min_count', 'max_count');
    }

    public function effects(): BelongsToMany
    {
        return $this->belongsToMany(Effect::class, 'monster_effects')
            ->withPivot('chance')
            ->withTimestamps();
    }

    public function getDodge(): int
    {
        return $this->dodge;
    }

    public function getCritical(): int
    {
        return $this->critical;
    }

    public function getArmor(): int
    {
        return $this->armor;
    }
}
