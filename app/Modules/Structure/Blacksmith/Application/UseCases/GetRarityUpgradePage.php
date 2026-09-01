<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\UseCases;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Backpack\Domain\Services\ItemTooltip\BackpackItemTooltipStrategy;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Application\ItemTooltip\Strategy\ShareItemTooltipStrategy;
use App\Modules\Structure\Blacksmith\Application\DTOs\RarityUpgradePageDTO;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

class GetRarityUpgradePage
{
    public function __construct(private readonly ItemTooltipCollector $collector) {}

    public function execute(User $user, int $blacksmithId): RarityUpgradePageDTO
    {
        $blacksmith = Structure::query()->findOrFail($blacksmithId);
        abort_unless($blacksmith->isBlacksmith(), 404);
        abort_unless((int) $blacksmith->location_id === (int) $user->location_id, 403);

        $slots = Backpack::query()
            ->with(['item.itemInfo.rarityUpgradeTarget', 'item.itemInfo.rarityUpgradeMaterials'])
            ->where('user_id', $user->id)
            ->where('equipped', false)
            ->whereHas('item.itemInfo', fn ($query) => $query->whereNotNull('upgrade_to_share_item_id'))
            ->get();

        $counts = DB::table('backpacks')
            ->join('items', 'items.id', '=', 'backpacks.item_id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', false)
            ->groupBy('items.share_item_id')
            ->selectRaw('items.share_item_id, SUM(backpacks.count) as count')
            ->pluck('count', 'share_item_id');

        $items = $slots->map(function (Backpack $slot) use ($counts, $user): array {
            $source = $slot->item->itemInfo;
            $materials = $source->rarityUpgradeMaterials->map(fn ($material): array => [
                'id' => (int) $material->id,
                'name' => $material->name,
                'image' => $material->transparent_image ?? $material->image,
                'needed' => (int) $material->pivot->count,
                'available' => (int) ($counts[$material->id] ?? 0),
            ])->values()->all();

            return [
                'itemId' => (int) $slot->item_id,
                'name' => $source->name,
                'image' => $source->transparent_image ?? $source->image,
                'rarity' => $source->rarity->label(),
                'rarityColor' => $source->rarity->color(),
                'targetId' => (int) $source->rarityUpgradeTarget->id,
                'targetName' => $source->rarityUpgradeTarget->name,
                'targetImage' => $source->rarityUpgradeTarget->transparent_image ?? $source->rarityUpgradeTarget->image,
                'targetRarity' => $source->rarityUpgradeTarget->rarity->label(),
                'targetRarityColor' => $source->rarityUpgradeTarget->rarity->color(),
                'gold' => (int) $source->upgrade_gold_cost,
                'materials' => $materials,
                'canUpgrade' => $user->money >= $source->upgrade_gold_cost
                    && collect($materials)->every(fn (array $material) => $material['available'] >= $material['needed']),
            ];
        })->values()->all();

        $shareItemsForTooltips = $slots
            ->flatMap(function (Backpack $slot) {
                $source = $slot->item->itemInfo;

                return collect([$source->rarityUpgradeTarget])
                    ->merge($source->rarityUpgradeMaterials);
            })
            ->filter()
            ->unique('id')
            ->values();

        return new RarityUpgradePageDTO(
            blacksmith: $blacksmith,
            items: $items,
            itemTooltipScript: $this->collector
                ->collectFrom(new BackpackItemTooltipStrategy($slots))
                ->collectFrom(new ShareItemTooltipStrategy($shareItemsForTooltips))
                ->renderScript(),
        );
    }
}
