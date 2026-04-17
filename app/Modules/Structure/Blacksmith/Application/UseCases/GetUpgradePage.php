<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\UseCases;

use App\Enums\ShareItemType;
use App\Models\Structure;
use App\Models\User;
use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Backpack\Domain\Services\ItemTooltip\BackpackItemTooltipStrategy;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Structure\Blacksmith\Application\DTOs\UpgradePageDTO;
use App\Modules\Structure\Blacksmith\Domain\Enums\UpgradeScrollType;
use App\Services\ItemTooltip\ItemTooltipCollector;

class GetUpgradePage
{
    public function __construct(
        private readonly ItemTooltipCollector $collector,
        private readonly PlayerStatService $statService,
    ) {}

    public function execute(User $user, int $blacksmithId): UpgradePageDTO
    {
        $blacksmith = Structure::findOrFail($blacksmithId);

        $upgradeableTypes = [
            ShareItemType::WEAPON->value,
            ShareItemType::SHIELD->value,
            ShareItemType::ARMOR->value,
            ShareItemType::BELT->value,
        ];

        $items = Backpack::select('backpacks.*')
            ->with(['item'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', 0)
            ->whereIn('share_items.type', $upgradeableTypes)
            ->get();

        $baseScrolls = Backpack::select('backpacks.*')
            ->with(['item'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', 0)
            ->where('share_items.type', ShareItemType::SCROLL->value)
            ->where('share_items.upgrade_scroll_type', UpgradeScrollType::BASE->value)
            ->get();

        $bonusScrolls = Backpack::select('backpacks.*')
            ->with(['item'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', 0)
            ->where('share_items.type', ShareItemType::SCROLL->value)
            ->whereIn('share_items.upgrade_scroll_type', [
                UpgradeScrollType::PROTECTION->value,
                UpgradeScrollType::STABILIZER->value,
                UpgradeScrollType::LUCKY->value,
            ])
            ->get();

        $itemTooltipScript = $this->collector
            ->collectFrom(new BackpackItemTooltipStrategy($items))
            ->collectFrom(new BackpackItemTooltipStrategy($baseScrolls))
            ->collectFrom(new BackpackItemTooltipStrategy($bonusScrolls))
            ->renderScript();

        $player = $user->player;
        $playerDecorator = $this->statService->resolve($player);

        return new UpgradePageDTO(
            blacksmith: $blacksmith,
            player: $player,
            playerDecorator: $playerDecorator,
            items: $items,
            baseScrolls: $baseScrolls,
            bonusScrolls: $bonusScrolls,
            itemTooltipScript: $itemTooltipScript,
        );
    }
}
