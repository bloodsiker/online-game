<?php

declare(strict_types=1);

namespace App\Modules\Structure\Auction\Application\DTOs;

final readonly class ExchangeFilterDTO
{
    public function __construct(
        public bool $matching,
        public ?string $name,
        public ?string $type,
        public ?int $countMin,
        public ?int $countMax,
    ) {}
}