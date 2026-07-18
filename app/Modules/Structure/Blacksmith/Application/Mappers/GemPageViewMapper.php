<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\Mappers;

use App\Modules\Backpack\Domain\Services\ItemTooltip\BackpackItemTooltipStrategy;
use App\Modules\Player\Domain\Enums\PlayerStatKey;
use App\Modules\Structure\Blacksmith\Application\DTOs\GemPageDTO;
use App\Modules\Structure\Blacksmith\Domain\Services\MountRarityConfig;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Services\ItemTooltip\ItemTooltipCollector;
use Illuminate\Support\Collection;

class GemPageViewMapper
{
    public function __construct(
        private readonly ItemTooltipCollector $collector,
    ) {}

    /**
     * @param  Collection<int, mixed>  $items
     * @param  Collection<int, mixed>  $gems
     * @param  Collection<int, mixed>  $mounts
     */
    public function map(Structure $blacksmith, Collection $items, Collection $gems, Collection $mounts): GemPageDTO
    {
        $itemTooltipScript = $this->collector
            ->collectFrom(new BackpackItemTooltipStrategy($items))
            ->collectFrom(new BackpackItemTooltipStrategy($gems))
            ->collectFrom(new BackpackItemTooltipStrategy($mounts))
            ->renderScript();

        return new GemPageDTO(
            blacksmith: $blacksmith,
            items: $items->map(fn ($slot) => [
                'id' => $slot->item->id,
                'name' => $slot->item->itemInfo->name,
                'image' => $slot->item->itemInfo->image,
                'upgradeLevel' => $slot->item->upgrade_lvl,
                'socketCount' => $slot->item->socket_count,
                'gemsCount' => $slot->item->gems->count(),
                'gems' => $slot->item->gems->map(fn ($gem) => [
                    'socket_index' => $gem->socket_index,
                    'share_item_id' => $gem->share_item_id,
                    'name' => $gem->gemInfo->name,
                    'img' => $gem->gemInfo->image,
                    'stats' => $this->translateStats($gem->gemInfo->gem_stats ?? []),
                ])->values()->all(),
            ])->values()->all(),
            gems: $gems->map(fn ($slot) => [
                'id' => $slot->item->id,
                'name' => $slot->item->itemInfo->name,
                'image' => $slot->item->itemInfo->image,
                'count' => $slot->count,
                'stats' => $this->translateStats($slot->item->itemInfo->gem_stats ?? []),
            ])->values()->all(),
            mounts: $mounts->map(function ($slot) {
                $rarity = $slot->item->itemInfo->rarity;
                [$min, $max] = MountRarityConfig::socketRange($rarity);

                return [
                    'id' => $slot->item->id,
                    'name' => $slot->item->itemInfo->name,
                    'image' => $slot->item->itemInfo->image,
                    'count' => $slot->count,
                    'rarity' => MountRarityConfig::label($rarity),
                    'socketMin' => $min,
                    'socketMax' => $max,
                    'openCost' => MountRarityConfig::openCost($rarity),
                ];
            })->values()->all(),
            itemTooltipScript: $itemTooltipScript,
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $stats
     * @return array<int, array<string, mixed>>
     */
    private function translateStats(array $stats): array
    {
        return array_map(function (array $stat): array {
            $stat['stat'] = PlayerStatKey::tryFrom($stat['stat'] ?? '')?->label() ?? ($stat['stat'] ?? '');

            return $stat;
        }, $stats);
    }
}
