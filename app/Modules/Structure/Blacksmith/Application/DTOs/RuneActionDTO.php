<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\DTOs;

use App\Modules\User\Infrastructure\Persistence\Models\User;

final readonly class RuneActionDTO
{
    /**
     * @param  array<int, int>  $lockedIndices
     */
    public function __construct(
        public User $user,
        public int $itemId,
        public ?int $runeId = null,
        public ?int $keyId = null,
        public ?int $slotIndex = null,
        public bool $riskMode = false,
        public array $lockedIndices = [],
    ) {}
}
