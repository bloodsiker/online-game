<?php

namespace App\Services\Combat\Boss\Mechanics;

use App\Services\Combat\Boss\BossFightContext;

/**
 * Механика смерти - урон при смерти
 */
class DeathExplosionMechanic extends BaseBossMechanic
{
    public function canTrigger(BossFightContext $context): bool
    {
        // Ця механіка спрацьовує тільки при смерті
        return $context->getBossCurrentHp() <= 0;
    }

    public function execute(BossFightContext $context): void
    {
        $damagePercent = $this->getConfig('damage_percent', 50);
        $maxHp = $context->getBossMaxHp();
        $damage = (int)(($maxHp * $damagePercent) / 100);

        $actualDamage = max(1, $damage - $context->getPlayer()->getArmor());
        $context->dealDamageToPlayer($actualDamage);

        $context->addLog(sprintf(
            '<p><b class="color-death">💀 %s взрывается при смерти! Получено %d урона!</b></p>',
            $context->getLocationMonster()->monster->name,
            $actualDamage
        ));
    }

    public function getDescription(): string
    {
        return "Босс взорвался при смерти!";
    }
}
