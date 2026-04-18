<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\UseCases;

use App\Modules\Item\Application\DTOs\ItemInfoPageDTO;
use App\Modules\Item\Application\Mappers\ItemInfoPageViewMapper;
use App\Modules\Item\Domain\Contracts\ItemReadRepository;

class GetItemInfoPage
{
    public function __construct(
        private readonly ItemReadRepository $readRepository,
        private readonly ItemInfoPageViewMapper $mapper,
    ) {}

    public function execute(int $itemId): ItemInfoPageDTO
    {
        $item = $this->readRepository->findItem($itemId);
        abort_if($item === null, 404);

        return $this->mapper->map($item);
    }
}
