<?php

declare(strict_types=1);

namespace App\Modules\User\Domain\Contracts;

use App\Modules\User\Infrastructure\Persistence\Models\User;

interface CharacterDetailsReader
{
    public function verified(int $userId): ?User;
}
