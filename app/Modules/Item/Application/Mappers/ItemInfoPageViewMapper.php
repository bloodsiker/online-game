<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\Mappers;

use App\Modules\Item\Application\DTOs\ItemInfoPageDTO;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipStatsBuilder;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Location\Infrastructure\Persistence\Models\LocationGate;
use App\Modules\Player\Domain\Enums\PlayerStatKey;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Share\Domain\Enums\ShareItemRequirementType;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItemRequirement;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareRecipe;

class ItemInfoPageViewMapper
{
    public function map(Item $item, ?Player $viewer = null): ItemInfoPageDTO
    {
        $name = (string) $item->getName();
        if ($item->upgrade_lvl > 0) {
            $name .= ' +'.$item->upgrade_lvl;
        }

        $item->loadMissing(['gems.gemInfo', 'runes.runeInfo']);

        return $this->build(
            shareItem: $item->itemInfo,
            name: $name,
            itemId: (int) $item->id,
            viewer: $viewer,
            handOverUrl: route('items.hand_over', ['id' => $item->id]),
            dropUrl: route('items.drop', ['id' => $item->id]),
            gems: ItemTooltipStatsBuilder::buildGems($item),
            runes: ItemTooltipStatsBuilder::buildRunes($item),
            upgradeLvl: $item->upgrade_lvl,
        );
    }

    /**
     * Каталожный просмотр по share_item_id (например, товар в магазине) —
     * без конкретного экземпляра Item, поэтому без уровня заточки и без
     * действий над предметом (передать/выбросить), которые применимы только
     * к реально принадлежащему игроку экземпляру.
     */
    public function mapFromShareItem(ShareItem $shareItem, ?Player $viewer = null): ItemInfoPageDTO
    {
        return $this->build(
            shareItem: $shareItem,
            name: (string) $shareItem->name,
            itemId: 0,
            viewer: $viewer,
            handOverUrl: '',
            dropUrl: '',
        );
    }

    private function build(
        ShareItem $shareItem,
        string $name,
        int $itemId,
        ?Player $viewer,
        string $handOverUrl,
        string $dropUrl,
        array $gems = [],
        array $runes = [],
        int $upgradeLvl = 0,
    ): ItemInfoPageDTO {
        $shareItem->loadMissing('stats', 'effects', 'requirements.skill', 'skill');

        $gatheringRequirement = null;
        if ($shareItem->type->isGatheringResource() && $shareItem->skill !== null) {
            $gatheringRequirement = [
                'skillName' => (string) $shareItem->skill->name,
                'level' => max(1, (int) $shareItem->skill_lvl),
            ];
        }

        $requirements = [];
        foreach ($shareItem->requirements as $requirement) {
            $requirements[] = [
                'label' => $requirement->label(),
                'value' => (int) $requirement->min_value,
                'met' => $this->isRequirementMet($viewer, $requirement),
            ];
        }

        return new ItemInfoPageDTO(
            itemId: $itemId,
            shareItemId: (int) $shareItem->id,
            name: $name,
            color: $shareItem->rarity?->color() ?? '#333333',
            image: $shareItem->image ? asset($shareItem->image) : null,
            typeName: (string) $shareItem->getTypeName(),
            price: (int) $shareItem->price,
            description: $shareItem->description,
            noGive: ! $shareItem->is_give,
            noWeight: ! $shareItem->is_weight,
            noSell: ! $shareItem->is_sell,
            gateLocations: $this->buildGateLocations((int) $shareItem->id),
            stats: ItemTooltipStatsBuilder::build($shareItem, $upgradeLvl),
            gatheringRequirement: $gatheringRequirement,
            requirements: $requirements,
            handOverUrl: $handOverUrl,
            dropUrl: $dropUrl,
            sameItemsUrl: route('backpack', ['sid' => $shareItem->id]),
            backpackUrl: route('backpack'),
            gems: $gems,
            runes: $runes,
            craftUsages: $this->buildCraftUsages($shareItem),
            recipeCraft: $this->buildRecipeCraft($shareItem),
        );
    }

    /**
     * Для книги рецепта показываем, что по нему изготавливается: результат,
     * профессию с уровнем и список ингредиентов.
     *
     * @return array{profession: string, level: int, resultName: string, resultColor: string, resultUrl: string, ingredients: list<array{name: string, color: string, url: string, count: int}>}|null
     */
    private function buildRecipeCraft(ShareItem $shareItem): ?array
    {
        if ($shareItem->type !== ShareItemType::RECIPE) {
            return null;
        }

        $recipe = ShareRecipe::query()
            ->where('share_item_id', $shareItem->id)
            ->with(['kraftItem', 'items'])
            ->first();

        if ($recipe === null || $recipe->kraftItem === null) {
            return null;
        }

        $ingredients = [];
        foreach ($recipe->items as $ingredient) {
            $ingredients[] = [
                'name' => (string) $ingredient->name,
                'color' => $ingredient->rarity?->color() ?? '#333333',
                'url' => route('items.info.share', ['id' => $ingredient->id]),
                'count' => max(1, (int) $ingredient->pivot->count),
            ];
        }

        return [
            'profession' => (string) ($shareItem->skill?->name ?? 'Профессия'),
            'level' => max(1, (int) $shareItem->skill_lvl),
            'resultName' => (string) $recipe->kraftItem->name,
            'resultColor' => $recipe->kraftItem->rarity?->color() ?? '#333333',
            'resultUrl' => route('items.info.share', ['id' => $recipe->kraftItem->id]),
            'ingredients' => $ingredients,
        ];
    }

    /**
     * Ресурс сам по себе не имеет профессии, поэтому показываем, в рецептах
     * какой профессии и какого уровня он используется.
     *
     * @return list<array{profession: string, level: int, resultName: string, resultUrl: string}>
     */
    private function buildCraftUsages(ShareItem $shareItem): array
    {
        if (! $shareItem->type->isGatheringResource()) {
            return [];
        }

        $recipes = ShareRecipe::query()
            ->whereHas('items', fn ($query) => $query->where('share_items.id', $shareItem->id))
            ->with(['itemInfo.skill', 'kraftItem'])
            ->get();

        $usages = [];
        foreach ($recipes as $recipe) {
            $skill = $recipe->itemInfo?->skill;
            if ($skill === null || $recipe->kraftItem === null) {
                continue;
            }

            $usages[] = [
                'profession' => (string) $skill->name,
                'level' => max(1, (int) $recipe->itemInfo->skill_lvl),
                'resultName' => (string) $recipe->kraftItem->name,
                'resultUrl' => route('items.info.share', ['id' => $recipe->kraftItem->id]),
            ];
        }

        usort($usages, static fn (array $a, array $b) => [$a['profession'], $a['level']] <=> [$b['profession'], $b['level']]);

        return $usages;
    }

    private function buildGateLocations(int $shareItemId): ?string
    {
        $gates = LocationGate::where('share_item_id', $shareItemId)
            ->with(['fromLocation', 'toLocation'])
            ->get();

        if ($gates->isEmpty()) {
            return null;
        }

        $pairs = [];
        foreach ($gates as $gate) {
            $ids = [$gate->from_location_id, $gate->to_location_id];
            sort($ids);
            $key = implode('-', $ids);

            if (isset($pairs[$key])) {
                continue;
            }

            $pairs[$key] = sprintf(
                '%s ↔ %s',
                $gate->fromLocation?->name ?: 'Локация #'.$gate->from_location_id,
                $gate->toLocation?->name ?: 'Локация #'.$gate->to_location_id,
            );
        }

        return implode(', ', $pairs);
    }

    private function isRequirementMet(?Player $viewer, ShareItemRequirement $requirement): bool
    {
        if ($viewer === null) {
            return true;
        }

        return match ($requirement->type) {
            ShareItemRequirementType::LEVEL => $viewer->lvl >= $requirement->min_value,
            ShareItemRequirementType::STAT => $this->statValue($viewer, $requirement->stat_key) >= $requirement->min_value,
            ShareItemRequirementType::SKILL => ($viewer->skills->firstWhere('skill_id', $requirement->skill_id)?->lvl ?? 0) >= $requirement->min_value,
        };
    }

    private function statValue(Player $viewer, ?string $statKey): int
    {
        return match (PlayerStatKey::tryFrom((string) $statKey)) {
            PlayerStatKey::STRENGTH => (int) floor($viewer->strength),
            PlayerStatKey::AGILITY => (int) floor($viewer->agility),
            PlayerStatKey::INTUITION => (int) floor($viewer->intuition),
            PlayerStatKey::WISDOM => (int) floor($viewer->wisdom),
            PlayerStatKey::INTELLIGENCE => (int) floor($viewer->intelligence),
            default => 0,
        };
    }
}
