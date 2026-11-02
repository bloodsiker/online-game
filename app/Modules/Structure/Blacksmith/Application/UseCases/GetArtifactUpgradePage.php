<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\UseCases;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Backpack\Domain\Services\ItemTooltip\BackpackItemTooltipStrategy;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Application\ItemTooltip\Strategy\ItemModelTooltipStrategy;
use App\Modules\Item\Application\ItemTooltip\Strategy\ShareItemTooltipStrategy;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Structure\Blacksmith\Domain\Services\ArtifactUpgradeRules;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;

final class GetArtifactUpgradePage
{
    public function __construct(private readonly ItemTooltipCollector $collector) {}

    public function execute(User $user, int $structureId): array
    {
        $blacksmith = Structure::query()->setEagerLoads([])->findOrFail($structureId);
        abort_unless($blacksmith->type === Structure::TYPE_BLACKSMITH, 404);
        abort_unless((int) $blacksmith->location_id === (int) $user->location_id, 403);
        $slots = Backpack::query()->with([
            'item.itemInfo' => fn ($query) => $query->without(['recipe', 'recipe.items']),
            'item.itemInfo.artifactUpgradeConfig.materials', 'item.itemInfo.stats',
        ])
            ->where('user_id', $user->id)->where('equipped', false)->where('count', '>', 0)
            ->whereHas('item', fn ($query) => $query->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now())))
            ->orderBy('id')->get();
        $available = $slots->filter(fn (Backpack $slot): bool => (int) $slot->item->upgrade_lvl === 0);
        $counts = $available->groupBy(fn (Backpack $slot): int => (int) $slot->item->share_item_id)->map->sum('count');
        $artifacts = $slots->filter(fn (Backpack $slot): bool => (int) $slot->count === 1
            && $slot->item->itemInfo->type === ShareItemType::ARTIFACT
            && $slot->item->itemInfo->artifactUpgradeConfig?->is_enabled);
        $upgrades = $artifacts->map(function (Backpack $slot) use ($counts, $user): array {
            $item = $slot->item;
            $source = $item->itemInfo;
            $config = $source->artifactUpgradeConfig;
            $donors = (int) ($counts[$source->id] ?? 0) - ((int) $item->upgrade_lvl === 0 ? 1 : 0);
            $materials = $config->materials->map(function ($material) use ($counts, $source, $item): array {
                $available = (int) ($counts[$material->id] ?? 0);
                if ((int) $material->id === (int) $source->id) {
                    $available -= 1 + ((int) $item->upgrade_lvl === 0 ? 1 : 0);
                }

                return ['item' => $material, 'needed' => (int) $material->pivot->count, 'available' => max(0, $available)];
            });

            return [
                'item' => $item, 'config' => $config, 'donors' => max(0, $donors), 'materials' => $materials,
                'canUpgrade' => $donors > 0 && (int) $item->upgrade_lvl < ArtifactUpgradeRules::MAX_LEVEL
                    && $user->money >= $config->gold_cost
                    && $materials->every(fn (array $material): bool => $material['available'] >= $material['needed']),
            ];
        });

        $templates = $artifacts->flatMap(fn (Backpack $slot) => collect([$slot->item->itemInfo])
            ->merge($slot->item->itemInfo->artifactUpgradeConfig->materials))->unique('id')->values();
        $previews = $artifacts->map(function (Backpack $slot) {
            $preview = clone $slot->item;
            $preview->upgrade_lvl = min(ArtifactUpgradeRules::MAX_LEVEL, (int) $preview->upgrade_lvl + 1);

            return $preview;
        });

        return [
            'blacksmith' => $blacksmith, 'user' => $user, 'artifactUpgrades' => $upgrades,
            'maxArtifactUpgradeLevel' => ArtifactUpgradeRules::MAX_LEVEL,
            'itemTooltipScript' => $this->collector
                ->collectFrom(new BackpackItemTooltipStrategy($artifacts))
                ->collectFrom(new ShareItemTooltipStrategy($templates, idPrefix: 'artifact_template_'))
                ->collectFrom(new ItemModelTooltipStrategy($previews, idPrefix: 'artifact_preview_'))
                ->renderScript(),
        ];
    }
}
