<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Application\DTOs;

final readonly class ShopResultDTO
{
    public function __construct(
        public bool $ok,
        public string $message,
    ) {}
}