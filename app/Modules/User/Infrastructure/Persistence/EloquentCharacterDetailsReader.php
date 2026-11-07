<?php

declare(strict_types=1);

namespace App\Modules\User\Infrastructure\Persistence;

use App\Modules\User\Domain\Contracts\CharacterDetailsReader;
use App\Modules\User\Infrastructure\Persistence\Models\User;

final class EloquentCharacterDetailsReader implements CharacterDetailsReader
{
    public function verified(int $userId): ?User
    {
        return User::query()->withOnly(['profile', 'player.race', 'player.skills.skill'])
            ->whereKey($userId)->whereNotNull('email_verified_at')->first();
    }
}
