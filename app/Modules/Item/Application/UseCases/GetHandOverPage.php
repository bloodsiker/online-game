<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\UseCases;

use App\Modules\Item\Application\DTOs\HandOverPageDTO;
use App\Modules\Item\Application\Mappers\HandOverPageViewMapper;
use App\Modules\Item\Domain\Contracts\ItemReadRepository;

class GetHandOverPage
{
    public function __construct(
        private readonly ItemReadRepository $readRepository,
        private readonly HandOverPageViewMapper $mapper,
    ) {}

    public function execute(int $itemId): HandOverPageDTO
    {
        $user = auth()->user();
        $item = $this->readRepository->findItem($itemId);

        abort_if($item === null || $user === null, 404);

        return $this->mapper->map(
            $item,
            $this->readRepository->getOnlineUsersOnLocation($user),
            false,
            false,
            null,
        );
    }
}
