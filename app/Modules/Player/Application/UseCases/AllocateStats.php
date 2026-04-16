<?php

declare(strict_types=1);

namespace App\Modules\Player\Application\UseCases;

use App\Events\PlayerChangeStat;
use App\Models\Player\Player;
use App\Modules\Player\Application\DTOs\AllocateStatsResultDTO;
use App\Modules\Player\Domain\Repositories\PlayerRepositoryInterface;

class AllocateStats
{
    public function __construct(
        private readonly PlayerRepositoryInterface $playerRepository,
    ) {}

    /**
     * @param array<string, int> $stats
     * @throws \DomainException
     */
    public function execute(Player $player, array $stats): AllocateStatsResultDTO
    {
        $sumChange = array_sum($stats);

        if ($sumChange === 0) {
            throw new \DomainException('Основные характеристики остались прежними.');
        }

        if ($sumChange > $player->getFreeStats()) {
            throw new \DomainException('У вас нет столько свободных характеристик.');
        }

        $player->strength     += $stats['strength'];
        $player->intuition    += $stats['intuition'];
        $player->agility      += $stats['agility'];
        $player->intelligence += $stats['intelligence'];
        $player->wisdom       += $stats['wisdom'];
        $player->free_stats   -= $sumChange;

        $this->playerRepository->save($player);
        event(new PlayerChangeStat($player));
        $player->refresh();

        return new AllocateStatsResultDTO(
            freeStats: $player->free_stats,
            strength: (int) $player->getStrength(),
            intuition: (int) $player->getInt(),
            agility: (int) $player->getAgility(),
            intelligence: (int) $player->getIntelligence(),
            wisdom: (int) $player->getMud(),
        );
    }
}