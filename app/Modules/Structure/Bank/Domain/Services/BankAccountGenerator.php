<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Domain\Services;

use App\Modules\User\Infrastructure\Persistence\Models\User;

class BankAccountGenerator
{
    public function generate(User $user): string
    {
        return str_pad((string) (10000000 + $user->id), 8, '0', STR_PAD_LEFT);
    }
}
