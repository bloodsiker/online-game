<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\DTOs;

use App\Modules\User\Infrastructure\Persistence\Models\User;

final readonly class TransferUpgradeDTO
{
    public function __construct(
        public User $user,
        public int $sourceItemId,
        public int $targetItemId,
        public int $transgressorShareItemId,
    ) {}
}
