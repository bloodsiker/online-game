<?php

declare(strict_types=1);

namespace App\Modules\Structure\Warehouse\Application\DTOs;

final readonly class WarehouseResultDTO
{
    public function __construct(
        public bool $ok,
        public string $message,
    ) {}
}
