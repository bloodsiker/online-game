<?php

declare(strict_types=1);

namespace App\Modules\Friend\Application\DTOs;

final readonly class FriendActionResultDTO
{
    public function __construct(
        public bool $ok,
        public string $message,
        public string $flashType,
    ) {}
}
