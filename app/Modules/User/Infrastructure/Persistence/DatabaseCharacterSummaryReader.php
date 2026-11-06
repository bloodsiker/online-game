<?php

declare(strict_types=1);

namespace App\Modules\User\Infrastructure\Persistence;

use App\Modules\User\Domain\Contracts\CharacterSummaryReader;
use Illuminate\Support\Facades\DB;

final class DatabaseCharacterSummaryReader implements CharacterSummaryReader
{
    public function read(int $userId): ?array
    {
        $row = DB::table('users')->join('players', 'players.id', '=', 'users.player_id')
            ->where('users.id', $userId)->whereNotNull('users.email_verified_at')
            ->first(['users.name', 'users.money', 'users.diamond', 'players.lvl']);

        return $row ? ['nickname' => $row->name, 'level' => (int) $row->lvl,
            'money' => (int) $row->money, 'diamonds' => number_format((float) $row->diamond, 2, '.', '')] : null;
    }
}
