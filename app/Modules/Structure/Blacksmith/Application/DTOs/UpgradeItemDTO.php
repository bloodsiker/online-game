<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\DTOs;

use App\Models\User;

final readonly class UpgradeItemDTO
{
    public function __construct(
        public User $user,
        public int $itemId,
        public int $baseScrollId,
        public ?int $bonusScrollId,
    ) {}
}
