<?php

declare(strict_types=1);

namespace App\Modules\User\Infrastructure\Persistence;

use App\Modules\User\Domain\Contracts\PublicCharacterReader;
use Illuminate\Support\Facades\DB;

final class DatabasePublicCharacterReader implements PublicCharacterReader
{
    public function find(int $userId): ?array
    {
        $character = DB::table('users')
            ->leftJoin('players', 'players.id', '=', 'users.player_id')
            ->where('users.id', $userId)
            ->first(['users.id', 'users.name', 'players.lvl']);

        return $character ? [
            'id' => (int) $character->id,
            'name' => $character->name,
            'level' => $character->lvl !== null ? (int) $character->lvl : null,
        ] : null;
    }
}
