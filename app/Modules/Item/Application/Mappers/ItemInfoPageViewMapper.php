<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\Mappers;

use App\Modules\Item\Application\DTOs\ItemInfoPageDTO;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Player\Domain\Enums\PlayerStatKey;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Share\Domain\Enums\ShareItemRequirementType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItemRequirement;
use App\Services\ItemTooltip\ItemTooltipStatsBuilder;

class ItemInfoPageViewMapper
{
    public function map(Item $item, ?Player $viewer = null): ItemInfoPageDTO
    {
        $shareItem = $item->itemInfo;
        $shareItem->loadMissing('stats', 'effects', 'requirements.skill');

        $name = (string) $item->getName();
        if ($item->upgrade_lvl > 0) {
            $name .= ' +'.$item->upgrade_lvl;
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
            itemId: (int) $item->id,
            shareItemId: (int) $shareItem->id,
            name: $name,
            color: $shareItem->rarity?->color() ?? '#333333',
            image: $shareItem->image ? asset($shareItem->image) : null,
            typeName: (string) $shareItem->getTypeName(),
            price: (int) $shareItem->price,
            description: $shareItem->description,
            noGive: ! $shareItem->is_sell,
            noWeight: ! $shareItem->is_weight,
            noSell: ! $shareItem->is_sell,
            stats: ItemTooltipStatsBuilder::build($shareItem),
            requirements: $requirements,
            handOverUrl: route('items.hand_over', ['id' => $item->id]),
            dropUrl: route('items.drop', ['id' => $item->id]),
            sameItemsUrl: route('backpack', ['sid' => $shareItem->id]),
            backpackUrl: route('backpack'),
        );
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
