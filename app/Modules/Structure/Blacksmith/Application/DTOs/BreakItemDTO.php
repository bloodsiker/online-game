<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\DTOs;

use App\Models\User;

final readonly class BreakItemDTO
{
    public function __construct(
        public User $user,
        public int $blacksmithId,
        public int $itemId,
    ) {}
}
