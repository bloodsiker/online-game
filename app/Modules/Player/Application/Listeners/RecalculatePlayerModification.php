<?php

namespace App\Modules\Player\Application\Listeners;

use App\Modules\Player\Domain\Events\PlayerChangeStat;
use App\Modules\Player\Domain\Services\PlayerStatFormulas;
use App\Modules\Player\Domain\Services\PlayerStatService;

class RecalculatePlayerModification
{
    public function __construct(private readonly PlayerStatService $statService) {}

    public function handle(PlayerChangeStat $event): void
    {
        $player = $event->player;

        $player->hp_max = PlayerStatFormulas::DEFAULT_HP
            + (PlayerStatFormulas::HP_PER_LEVEL * (max(1, (int) $player->lvl) - 1))
            + (PlayerStatFormulas::HP_PER_ENDURANCE * max(0, $player->getEndurance() - 1));
        $player->mp_max = PlayerStatFormulas::DEFAULT_MP
            + (PlayerStatFormulas::MP_PER_MUD * ($player->getMud() - 1));

        $player->save();

        $this->statService->invalidate($player);
        $sheet = $this->statService->resolve($player);
        if ($event->restoreResources) {
            // Повышение уровня: полностью восстанавливаем HP/MP до максимума с учётом бонусов.
            $player->hp_now = $sheet->getHpMax();
            $player->mp_now = $sheet->getMpMax();
        } else {
            // Распределение/сброс характеристик не лечит: текущие HP/MP сохраняются (не выше нового максимума).
            $player->hp_now = min((int) $player->hp_now, $sheet->getHpMax());
            $player->mp_now = min((int) $player->mp_now, $sheet->getMpMax());
        }
        $player->save();
    }
}
