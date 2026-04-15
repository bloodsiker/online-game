<?php

namespace App\Services\Recovery\Strategies;

use App\Models\Player\Player;
use App\Models\Structure;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Services\Recovery\Dto\RecoveryResultDto;

class FullHealStrategy implements RecoveryStrategyInterface
{
    public function __construct(private PlayerStatService $statService) {}

    public function recover(Player $player, Structure $structure): RecoveryResultDto
    {
        $sheet = $this->statService->resolve($player);
        $hpMax = $sheet->getHpMax();
        $mpMax = $sheet->getMpMax();

        $healHp = $hpMax - $player->hp_now;
        $healMp = $mpMax - $player->mp_now;
        $player->hp_now = $hpMax;
        $player->mp_now = $mpMax;
        $player->save();

        return new RecoveryResultDto($player, $healHp, $healMp, []);
    }
}
