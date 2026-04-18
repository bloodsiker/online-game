<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\DTOs;

final readonly class ItemActionResultDTO
{
    public function __construct(
        public bool $ok,
        public string $message = '',
        public bool $hotbarRefresh = false,
    ) {}
}
