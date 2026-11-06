<?php

declare(strict_types=1);

namespace App\Modules\Chat\Domain\Contracts;

use App\Modules\User\Infrastructure\Persistence\Models\User;

interface ChatParticipantReader
{
    public function verified(int $userId): ?User;

    public function byNickname(string $nickname): ?User;
}
