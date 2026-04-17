<?php

declare(strict_types=1);

namespace App\Modules\Structure\Exchange\Application\DTOs;

final readonly class ExchangeResultDTO
{
    public function __construct(
        public bool $ok,
        public string $message,
    ) {}
}
