<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\DTOs;

use App\Modules\User\Infrastructure\Persistence\Models\User;

final readonly class GemActionDTO
{
    public function __construct(
        public User $user,
        public int $itemId,
        public ?int $gemId = null,
        public ?int $mountId = null,
        public ?int $socketIndex = null,
    ) {}
}
