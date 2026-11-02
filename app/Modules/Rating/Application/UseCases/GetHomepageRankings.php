<?php

declare(strict_types=1);

namespace App\Modules\Rating\Application\UseCases;

use App\Modules\Clan\Domain\Models\Clan;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;

final class GetHomepageRankings
{
    public function execute(): array
    {
        return [
            'topPlayers' => Player::query()
                ->whereHas('user')
                ->with([
                    'user' => fn ($query) => $query->without('player')->with([
                        'clanMembership.clan' => fn ($query) => $query->without('owner'),
                    ]),
                ])
                ->orderByDesc('lvl')->orderByDesc('exp')->orderBy('id')
                ->limit(10)->get(),
            'topClans' => Clan::query()->without('owner')
                ->orderByDesc('lvl')->orderByDesc('experience')->orderBy('name')->orderBy('id')
                ->limit(10)->get(),
        ];
    }
}
