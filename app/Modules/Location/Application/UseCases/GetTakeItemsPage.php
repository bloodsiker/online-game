<?php

declare(strict_types=1);

namespace App\Modules\Location\Application\UseCases;

use App\Modules\Location\Application\DTOs\TakeItemsPageDTO;
use App\Modules\Location\Application\Mappers\TakeItemsPageViewMapper;
use App\Modules\Location\Domain\Contracts\LocationReadRepository;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Application\ItemTooltip\Strategy\ShareItemTooltipStrategy;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class GetTakeItemsPage
{
    public function __construct(
        private readonly LocationReadRepository $readRepository,
        private readonly TakeItemsPageViewMapper $mapper,
        private readonly ItemTooltipCollector $tooltipCollector,
    ) {}

    public function execute(User $user): TakeItemsPageDTO
    {
        $items = $this->readRepository->getItemsOnLocation($user, $user->location_id);
        $page = $this->mapper->map($items);

        $this->tooltipCollector->collectFrom(new ShareItemTooltipStrategy(
            $items->map(static fn ($item) => $item->item->itemInfo),
        ));

        return new TakeItemsPageDTO(
            count: $page->count,
            items: $page->items,
            backUrl: $page->backUrl,
            itemTooltipScript: $this->tooltipCollector->renderScript(),
        );
    }
}
