<?php

declare(strict_types=1);

namespace App\Modules\Structure\Warehouse\Application\DTOs;

final readonly class WarehousePageDTO
{
    /**
     * @param  list<WarehouseItemDTO>  $items
     */
    public function __construct(
        public int $warehouseId,
        public int $money,
        public int $diamonds,
        public int $countInWarehouse,
        public int $warehouseCapacity,
        public array $items,
    ) {}
}
