<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\UseCases;

use App\Modules\Item\Application\DTOs\ItemInfoPageDTO;
use App\Modules\Item\Application\Mappers\ItemInfoPageViewMapper;
use App\Modules\Item\Domain\Contracts\ItemReadRepository;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;

class GetItemInfoPage
{
    public function __construct(
        private readonly ItemReadRepository $readRepository,
        private readonly ItemInfoPageViewMapper $mapper,
    ) {}

    public function execute(int $itemId, ?Player $viewer = null): ItemInfoPageDTO
    {
        $item = $this->readRepository->findItem($itemId);
        abort_if($item === null, 404);

        return $this->mapper->map($item, $viewer);
    }

    public function executeByShareItemId(int $shareItemId, ?Player $viewer = null): ItemInfoPageDTO
    {
        $item = $this->readRepository->findItemByShareItemId($shareItemId);
        abort_if($item === null, 404);

        return $this->mapper->map($item, $viewer);
    }
}
