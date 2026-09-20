<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\UseCases;

use App\Modules\Item\Application\DTOs\ChestPageDTO;
use App\Modules\Item\Application\Mappers\ChestPageViewMapper;
use App\Modules\Item\Domain\Contracts\ItemReadRepository;
use App\Modules\Item\Domain\Services\ItemService;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class GetChestPage
{
    public function __construct(
        private readonly ItemReadRepository $readRepository,
        private readonly ChestPageViewMapper $mapper,
        private readonly ItemService $itemService,
    ) {}

    public function execute(User $user, int $chestId, string $message = ''): ChestPageDTO
    {
        if ($this->itemService->accessibleChest($user, $chestId) === null) {
            abort(404);
        }

        return $this->mapper->map(
            $this->readRepository->findChestWithItems($chestId),
            $message,
            (int) $user->money,
        );
    }
}
