<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\UseCases;

use App\Enums\ShareItemType;
use App\Models\Share\ShareItem;
use App\Models\Structure;
use App\Models\User;
use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Backpack\Domain\Services\ItemTooltip\BackpackItemTooltipStrategy;
use App\Modules\Structure\Blacksmith\Application\DTOs\BreakPageDTO;
use App\Services\ItemTooltip\ItemTooltipCollector;

class GetBreakPage
{
    public function __construct(
        private readonly ItemTooltipCollector $collector,
    ) {}

    public function execute(User $user, int $blacksmithId): BreakPageDTO
    {
        $blacksmith = Structure::findOrFail($blacksmithId);
        $crystal = ShareItem::findOrFail(23);

        $items = Backpack::select('backpacks.*')
            ->with(['item'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', 0)
            ->whereIn('share_items.type', [ShareItemType::WEAPON->value, ShareItemType::SHIELD->value, ShareItemType::ARMOR->value])
            ->get();

        $itemTooltipScript = $this->collector
            ->collectFrom(new BackpackItemTooltipStrategy($items))
            ->renderScript();

        return new BreakPageDTO(
            blacksmith: $blacksmith,
            items: $items,
            crystal: $crystal,
            itemTooltipScript: $itemTooltipScript,
        );
    }
}
