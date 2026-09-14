<?php

declare(strict_types=1);

namespace App\Modules\Clan\Domain\Services;

use App\Modules\Clan\Domain\Enums\ClanLogAction;
use App\Modules\Clan\Domain\Models\Clan;
use App\Modules\Clan\Domain\Models\ClanLog;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class ClanLogService
{
    public function write(
        Clan|int $clan,
        User|int|null $user,
        ClanLogAction $action,
        ?string $details = null,
    ): ClanLog {
        return ClanLog::create([
            'clan_id' => $clan instanceof Clan ? $clan->getKey() : $clan,
            'user_id' => $user instanceof User ? $user->getKey() : $user,
            'action' => $action,
            'details' => $details,
        ]);
    }
}
