<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Application\DTOs;

final readonly class BankResultDTO
{
    public function __construct(
        public bool $ok,
        public string $message,
    ) {}
}
