<?php

namespace App\Services\Combat\Boss\Mechanics;

use App\Services\Combat\Boss\BossFightContext;

/**
 * Масова атака по гравцю
 */
class AoeAttackMechanic extends BaseBossMechanic
{
    public function execute(BossFightContext $context): void
    {
        $damagePercent = $this->getConfig('damage_percent', 30);
        $monster = $context->getLocationMonster()->monster;
//        $baseDamage = $monster->attack;
        $baseDamage = mt_rand($monster->min_dmg, $monster->max_dmg);

        $damage = (int)(($baseDamage * $damagePercent) / 100);
        $actualDamage = max(1, $damage - $context->getPlayer()->getArmor());

        $context->dealDamageToPlayer($actualDamage);

        $context->addLog(sprintf(
            '<p><b class="color-aoe">💥 %s использует массовую атаку! Получено %d урона!</b></p>',
            $monster->name,
            $actualDamage
        ));

        $this->markAsTriggered($context);
    }

    public function getDescription(): string
    {
        $percent = $this->getConfig('damage_percent', 30);
        return "Босс атакует всех ({$percent}% от базового урона)";
    }
}
