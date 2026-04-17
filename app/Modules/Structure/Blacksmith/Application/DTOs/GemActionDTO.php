<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\DTOs;

use App\Models\User;

final readonly class GemActionDTO
{
    public function __construct(
        public User $user,
        public int $itemId,
        public ?int $gemId = null,
        public ?int $kitId = null,
        public ?int $socketIndex = null,
    ) {}
}
