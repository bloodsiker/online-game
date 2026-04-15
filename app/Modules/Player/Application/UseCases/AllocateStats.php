<?php

declare(strict_types=1);

namespace App\Modules\Player\Application\UseCases;

use App\Events\PlayerChangeStat;
use App\Models\Player\Player;
use App\Modules\Player\Domain\Repositories\PlayerRepositoryInterface;

class AllocateStats
{
    public function __construct(
        private readonly PlayerRepositoryInterface $playerRepository,
    ) {}

    /**
     * @param array<string, int> $stats
     */
    public function execute(Player $player, array $stats): void
    {
        $player->strength     += $stats['strength'];
        $player->intuition    += $stats['intuition'];
        $player->agility      += $stats['agility'];
        $player->intelligence += $stats['intelligence'];
        $player->wisdom       += $stats['wisdom'];
        $player->free_stats   -= array_sum($stats);

        $this->playerRepository->save($player);

        event(new PlayerChangeStat($player));
    }
}