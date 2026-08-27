<?php

declare(strict_types=1);

namespace App\Modules\Structure\ReputationExchange\Application\DTOs;

final readonly class ReputationExchangeResultDTO
{
    public function __construct(
        public bool $success,
        public string $message,
    ) {}
}
