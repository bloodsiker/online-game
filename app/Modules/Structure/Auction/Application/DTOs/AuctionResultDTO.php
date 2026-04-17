<?php

declare(strict_types=1);

namespace App\Modules\Structure\Auction\Application\DTOs;

final readonly class AuctionResultDTO
{
    public function __construct(
        public bool $ok,
        public string $message,
    ) {}
}
