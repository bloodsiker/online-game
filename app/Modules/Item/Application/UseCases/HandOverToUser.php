<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\UseCases;

use App\Modules\Item\Application\DTOs\HandOverPageDTO;
use App\Modules\Item\Application\Mappers\HandOverPageViewMapper;
use App\Modules\Item\Domain\Contracts\ItemReadRepository;
use App\Modules\Item\Domain\Services\ItemService;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class HandOverToUser
{
    public function __construct(
        private readonly ItemReadRepository $readRepository,
        private readonly ItemService $itemService,
        private readonly HandOverPageViewMapper $mapper,
    ) {}

    public function execute(User $user, int $itemId, ?int $toUserId): HandOverPageDTO
    {
        $item = $this->readRepository->findItem($itemId);
        abort_if($item === null, 404);

        $toUser = $toUserId !== null ? $this->readRepository->findUser($toUserId) : null;
        $isHandedItem = false;
        $isUserMoved = false;

        if ($toUser !== null) {
            $error = $this->itemService->handOver($user, $item, $toUser);

            if ($error !== null) {
                $isUserMoved = str_contains($error, 'рядом');
                session()->flash('message', $error);
            } else {
                $isHandedItem = true;
            }
        }

        return $this->mapper->map(
            $item,
            $this->readRepository->getOnlineUsersOnLocation($user),
            $isHandedItem,
            $isUserMoved,
            $toUser,
        );
    }
}
