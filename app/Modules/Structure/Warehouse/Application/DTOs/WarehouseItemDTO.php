<?php

declare(strict_types=1);

namespace App\Modules\Structure\Warehouse\Application\DTOs;

final readonly class WarehouseItemDTO
{
    public function __construct(
        public int $id,
        public int $itemId,
        public int $count,
        public string $image,
        public string $name,
        public string $typeName,
        public string $infoUrl,
    ) {}
}
