<?php

declare(strict_types=1);

namespace App\Modules\ItemAssembly\Application\DTOs;

final readonly class AssemblyResult
{
    public function __construct(
        public bool $ok,
        public string $message,
        public ?int $itemId = null,
        public int $count = 0,
    ) {}
}
