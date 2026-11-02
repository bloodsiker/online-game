<?php

declare(strict_types=1);

namespace App\Modules\TreasureHunt\Infrastructure\Persistence\Models;

use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\Share\Infrastructure\Localization\HasLegacyTranslations;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TreasureMapConfig extends Model
{
    use HasLegacyTranslations;

    public $translatable = ['clue'];

    protected $guarded = [];

    protected $casts = ['is_active' => 'boolean', 'success_chance_percent' => 'float'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'share_item_id');
    }

    public function reward(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'reward_share_item_id');
    }

    public function reputation(): BelongsTo
    {
        return $this->belongsTo(Reputation::class);
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'treasure_map_locations');
    }
}
