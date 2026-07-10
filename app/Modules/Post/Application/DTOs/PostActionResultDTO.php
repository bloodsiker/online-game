<?php

declare(strict_types=1);

namespace App\Modules\Post\Application\DTOs;

final readonly class PostActionResultDTO
{
    public function __construct(
        public bool $ok,
        public string $message,
    ) {}
}
