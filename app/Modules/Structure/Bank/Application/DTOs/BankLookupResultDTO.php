<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Application\DTOs;

final readonly class BankLookupResultDTO
{
    public function __construct(
        public bool $ok,
        public ?string $name = null,
        public ?string $error = null,
        public int $status = 200,
    ) {}
}
