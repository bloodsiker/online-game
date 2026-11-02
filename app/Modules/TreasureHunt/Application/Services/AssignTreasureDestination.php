<?php

declare(strict_types=1);

namespace App\Modules\TreasureHunt\Application\Services;

use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\TreasureHunt\Infrastructure\Persistence\Models\TreasureHunt;
use App\Modules\TreasureHunt\Infrastructure\Persistence\Models\TreasureMapConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

final class AssignTreasureDestination
{
    public function __construct(private readonly TreasureSearchRandom $random) {}

    /** Called inside the inventory insert transaction, including pivot attach(). */
    public function execute(int $userId, int $itemId, ?Item $knownItem = null): void
    {
        // Pickups and normal inventory issuance already have this metadata loaded.
        // Keep the database lookup as a fallback for ID-only/pivot insertion paths.
        if ($knownItem !== null && (int) $knownItem->id === $itemId && $knownItem->relationLoaded('itemInfo')
            && $knownItem->getRelation('itemInfo') !== null
            && $knownItem->getRelation('itemInfo')?->type !== ShareItemType::TREASURE_MAP) {
            return;
        }
        $item = DB::table('items')->join('share_items', 'share_items.id', '=', 'items.share_item_id')
            ->where('items.id', $itemId)->where('share_items.type', 'treasure_map')
            ->select('items.id', 'items.share_item_id')->lockForUpdate()->first();
        if ($item === null || TreasureHunt::where('map_item_id', $itemId)->exists()) {
            return;
        }
        $config = TreasureMapConfig::where('share_item_id', $item->share_item_id)->first();
        if ($config === null) {
            Log::error('Treasure map item has no search configuration.', [
                'share_item_id' => (int) $item->share_item_id, 'item_id' => $itemId, 'user_id' => $userId,
            ]);
            throw ValidationException::withMessages(['treasure' => __('treasure.map_configuration_missing', ['id' => $item->share_item_id])]);
        }
        $maps = Location::whereNull('dungeon_id')->whereNotNull('map_id')->distinct()->orderBy('map_id')->pluck('map_id')->all();
        if ($maps === []) {
            throw ValidationException::withMessages(['treasure' => __('treasure.unavailable')]);
        }
        // Choose a territory first: more locations must not increase its chance.
        $mapId = $this->random->territory($maps);
        $locations = Location::where('map_id', $mapId)->whereNull('dungeon_id')->orderBy('id')->pluck('id')->all();
        if ($locations === []) {
            throw ValidationException::withMessages(['treasure' => __('treasure.unavailable')]);
        }
        TreasureHunt::create([
            'user_id' => $userId, 'map_item_id' => $itemId, 'treasure_map_config_id' => $config->id,
            'target_map_id' => $mapId,
            'target_location_id' => $this->random->location($locations),
        ]);
    }
}
