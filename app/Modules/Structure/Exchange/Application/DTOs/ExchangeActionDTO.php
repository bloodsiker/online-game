<?php

declare(strict_types=1);

namespace App\Modules\Structure\Exchange\Application\DTOs;

use App\Models\User;

final readonly class ExchangeActionDTO
{
    public function __construct(
        public User $user,
        public int $exchangeId,
        public int $fromShareId,
        public int $toShareId,
        public int $count,
    ) {}
}
