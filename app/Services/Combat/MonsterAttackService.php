<?php

namespace App\Services\Combat;

use App\DTO\AttackResultDTO;
use App\Models\Monster\MonsterOnLocation;

readonly class MonsterAttackService
{
    public function __construct(private HitCalculator $hitCalc)
    {
    }

    public function execute(FightHitInterface $player, MonsterOnLocation $locationMonster, AttackResultDTO $result): void
    {
        $hit = $this->hitCalc->monsterHit($locationMonster->monster, $player, $locationMonster->monster->min_dmg, $locationMonster->monster->max_dmg);

        if ($hit->isDodge()) {
            $result->log(sprintf('<p>%s атакует неудачно... Вы <b class="color-green">увернулись</b></p>', $locationMonster->monster->name));
            return;
        }

        $player->hp_now = max(0, $player->hp_now - $hit->getDamage());

        $msg = $hit->isCritical()
            ? sprintf('<p>%s прыгнул на вас. <b class="color-red">нанесен критический удар!</b> <br>Повреждения: <b>%s</b></p>', $locationMonster->monster->name, $hit->getDamage())
            : sprintf('<p>%s прыгнул на вас. <br>Повреждения: <b>%s</b></p>', $locationMonster->monster->name, $hit->getDamage());

        $result->log($msg);
    }
}
