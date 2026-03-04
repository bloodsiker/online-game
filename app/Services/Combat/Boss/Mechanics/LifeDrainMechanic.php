<?php

namespace App\Services\Combat\Boss\Mechanics;

use App\Services\Combat\Boss\BossFightContext;

/**
 * Висмоктування життя - завдає урон і лікується на цю ж кількість
 */
class LifeDrainMechanic extends BaseBossMechanic
{
    public function execute(BossFightContext $context): void
    {
        $drainPercent = $this->getConfig('drain_percent', 20);
        $monster = $context->getLocationMonster()->monster;

        // Розраховуємо урон від висмоктування
        $baseDamage = ($monster->min_dmg + $monster->max_dmg) / 2;
        $drainDamage = (int)(($baseDamage * $drainPercent) / 100);

        $player = $context->getPlayer();
        $actualDamage = max(1, $drainDamage - $player->getArmor());

        // Завдаємо урон гравцю
        $context->dealDamageToPlayer($actualDamage);

        // Лікуємо боса на цю ж кількість
        $oldHp = $context->getBossCurrentHp();
        $context->healBoss($actualDamage);
        $actualHeal = $context->getBossCurrentHp() - $oldHp;

        $context->addLog(sprintf(
            '<p><b class="color-life-drain">🩸 %s высасывает жизнь! Вы получили %d урон, босс восстановил %d HP!</b></p>',
            $monster->name,
            $actualDamage,
            $actualHeal
        ));

        $this->markAsTriggered($context);
    }

    public function getDescription(): string
    {
        $percent = $this->getConfig('drain_percent', 20);
        return "Босс высасывает жизнь ({$percent}% от атаки)";
    }
}
