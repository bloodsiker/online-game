<?php

declare(strict_types=1);

namespace App\Modules\Structure\PremiumShop\Application\DTOs;

final readonly class PremiumShopResultDTO
{
    public function __construct(
        public bool $ok,
        public string $message,
    ) {}
}