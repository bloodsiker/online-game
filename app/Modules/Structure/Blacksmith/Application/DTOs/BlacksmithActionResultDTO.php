<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\DTOs;

final readonly class BlacksmithActionResultDTO
{
    public function __construct(
        public bool $ok,
        public string $message,
        public bool $success = false,
        public bool $destroyed = false,
    ) {}
}
