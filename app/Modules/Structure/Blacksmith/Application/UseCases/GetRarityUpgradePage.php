<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\UseCases;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Backpack\Domain\Services\ItemTooltip\BackpackItemTooltipStrategy;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Application\ItemTooltip\Strategy\ShareItemTooltipStrategy;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Structure\Blacksmith\Application\DTOs\RarityUpgradePageDTO;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GetRarityUpgradePage
{
    public function __construct(private readonly ItemTooltipCollector $collector) {}

    public function execute(
        User $user,
        int $structureId,
        string $expectedStructureType = Structure::TYPE_BLACKSMITH,
        ?ShareItemType $itemType = null,
    ): RarityUpgradePageDTO {
        $blacksmith = Structure::query()->findOrFail($structureId);
        abort_unless($blacksmith->type === $expectedStructureType, 404);
        abort_unless((int) $blacksmith->location_id === (int) $user->location_id, 403);

        $slots = Backpack::query()
            ->with(['item.itemInfo.rarityUpgradeTarget', 'item.itemInfo.rarityUpgradeMaterials'])
            ->where('user_id', $user->id)
            ->where('equipped', false)
            ->whereHas('item.itemInfo', function ($query) use ($itemType): void {
                $query->whereNotNull('upgrade_to_share_item_id');

                if ($itemType !== null) {
                    $query->where('type', $itemType->value);
                }
            })
            ->get();

        $counts = DB::table('backpacks')
            ->join('items', 'items.id', '=', 'backpacks.item_id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', false)
            ->groupBy('items.share_item_id')
            ->selectRaw('items.share_item_id, SUM(backpacks.count) as count')
            ->pluck('count', 'share_item_id');

        $tooltipItems = collect();

        $items = $slots->map(function (Backpack $slot) use ($counts, $user, $tooltipItems): array {
            $source = $slot->item->itemInfo;

            $steps = $this->buildUpgradeSteps($source, $counts, $user, $tooltipItems);
            $firstStep = $steps[0];

            return [
                'itemId' => (int) $slot->item_id,
                'name' => $firstStep['name'],
                'steps' => $steps,
            ];
        })->values()->all();

        $shareItemsForTooltips = $slots
            ->flatMap(fn (Backpack $slot) => collect([$slot->item->itemInfo]))
            ->merge($tooltipItems)
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

    /**
     * @param  Collection<int, int>  $counts
     * @param  Collection<int, ShareItem>  $tooltipItems
     * @return list<array<string, mixed>>
     */
    private function buildUpgradeSteps(ShareItem $source, Collection $counts, User $user, Collection $tooltipItems): array
    {
        $steps = [];
        $visited = [];
        $current = $source;

        while ($current !== null && ! isset($visited[$current->id])) {
            $visited[$current->id] = true;
            $current->loadMissing(['rarityUpgradeTarget', 'rarityUpgradeMaterials']);

            $target = $current->rarityUpgradeTarget;
            if ($target === null) {
                break;
            }

            $materials = $current->rarityUpgradeMaterials->map(fn (ShareItem $material): array => [
                'id' => (int) $material->id,
                'name' => $material->name,
                'image' => $material->transparent_image ?? $material->image,
                'needed' => (int) $material->pivot->count,
                'available' => (int) ($counts[$material->id] ?? 0),
            ])->values()->all();

            $steps[] = [
                'name' => $current->name,
                'image' => $current->transparent_image ?? $current->image,
                'rarity' => $current->rarity->label(),
                'rarityColor' => $current->rarity->color(),
                'targetId' => (int) $target->id,
                'targetName' => $target->name,
                'targetImage' => $target->transparent_image ?? $target->image,
                'targetRarity' => $target->rarity->label(),
                'targetRarityColor' => $target->rarity->color(),
                'gold' => (int) $current->upgrade_gold_cost,
                'materials' => $materials,
                'canUpgrade' => $user->money >= $current->upgrade_gold_cost
                    && collect($materials)->every(fn (array $material) => $material['available'] >= $material['needed']),
            ];

            $tooltipItems->push($target);
            $tooltipItems->push(...$current->rarityUpgradeMaterials);
            $current = $target;
        }

        return $steps;
    }
}
