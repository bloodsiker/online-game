<?php

declare(strict_types=1);

namespace App\Modules\Structure\Warehouse\Application\UseCases;

use App\Models\Structure;
use App\Modules\Structure\Warehouse\Application\DTOs\WarehousePageDTO;
use App\Modules\Structure\Warehouse\Application\Mappers\WarehousePageViewMapper;
use App\Modules\Structure\Warehouse\Domain\Contracts\WarehouseInventoryRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class GetPutPage
{
    public function __construct(
        private readonly WarehouseInventoryRepository $inventoryRepository,
        private readonly WarehousePageViewMapper $mapper,
    ) {}

    public function execute(User $user, Structure $warehouse): WarehousePageDTO
    {
        return $this->mapper->map(
            $user,
            $warehouse->id,
            $this->inventoryRepository->countWarehouseItems($user->id, $warehouse->id),
            $this->inventoryRepository->getBackpackItems($user->id),
        );
    }
}
