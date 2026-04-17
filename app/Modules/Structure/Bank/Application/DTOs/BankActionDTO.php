<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Application\DTOs;

use App\Modules\User\Infrastructure\Persistence\Models\User;

final readonly class BankActionDTO
{
    public function __construct(
        public User $user,
        public int $amount = 0,
        public string $account = '',
    ) {}
}
