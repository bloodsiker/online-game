<?php

namespace App\Modules\Battle\Application\Services\Combat\Boss\Mechanics;

use App\Modules\Battle\Application\Services\BattleLogTranslator;
use App\Modules\Battle\Application\Services\Combat\Boss\BossFightContext;

/**
 * Регенерація HP
 */
class RegenerationMechanic extends BaseBossMechanic
{
    public function execute(BossFightContext $context): void
    {
        $healPercent = $this->getConfig('heal_percent', 10);
        $maxHp = $context->getBossMaxHp();
        $healAmount = (int) (($maxHp * $healPercent) / 100);

        $oldHp = $context->getBossCurrentHp();
        $context->healBoss($healAmount);
        $newHp = $context->getBossCurrentHp();
        $actualHeal = $newHp - $oldHp;

        $context->addLog(BattleLogTranslator::format(
            '<p><b class="color-heal">💚 %s регенерирует %d HP!</b></p>',
            BattleLogTranslator::contentName($context->getLocationMonster()->monster),
            $actualHeal
        ));

        $this->markAsTriggered($context);
    }

    public function getDescription(): string
    {
        $percent = $this->getConfig('heal_percent', 10);

        return "Босс восстановил {$percent}% своего здоровья";
    }
}
