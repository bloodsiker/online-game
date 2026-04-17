<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\UseCases;

use App\Enums\ShareItemType;
use App\Models\Structure;
use App\Models\User;
use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Backpack\Domain\Services\ItemTooltip\BackpackItemTooltipStrategy;
use App\Modules\Structure\Blacksmith\Application\DTOs\KraftPageDTO;
use App\Services\ItemTooltip\ItemTooltipCollector;
use App\Services\ItemTooltip\Strategy\ShareItemTooltipStrategy;
use Illuminate\Support\Facades\DB;

class GetKraftPage
{
    public function __construct(
        private readonly ItemTooltipCollector $collector,
    ) {}

    public function execute(User $user, int $blacksmithId): KraftPageDTO
    {
        $blacksmith = Structure::findOrFail($blacksmithId);

        $recipes = Backpack::select('backpacks.*')
            ->with(['item'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('share_items.type', ShareItemType::RECIPE->value)
            ->get();

        $resources = DB::table('share_items')
            ->select(['share_items.id', 'backpacks.count'])
            ->join('items', 'items.share_item_id', '=', 'share_items.id')
            ->join('backpacks', 'backpacks.item_id', '=', 'items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', 0)
            ->where('share_items.type', ShareItemType::RESOURCE)
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'count' => $item->count,
            ])
            ->toArray();

        $ingredientItems = $recipes->flatMap(fn ($recipe) => $recipe->item->itemInfo->recipe?->items ?? collect());

        $itemTooltipScript = $this->collector
            ->collectFrom(new BackpackItemTooltipStrategy($recipes))
            ->collectFrom(new ShareItemTooltipStrategy($ingredientItems))
            ->renderScript();

        return new KraftPageDTO(
            blacksmith: $blacksmith,
            recipes: $recipes,
            resources: $resources,
            itemTooltipScript: $itemTooltipScript,
        );
    }
}
