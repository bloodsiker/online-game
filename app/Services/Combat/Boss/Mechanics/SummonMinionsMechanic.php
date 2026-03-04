<?php

namespace App\Services\Combat\Boss\Mechanics;

use App\Services\Combat\Boss\BossFightContext;

/**
 * Вызов мобов
 */
class SummonMinionsMechanic extends BaseBossMechanic
{
    public function execute(BossFightContext $context): void
    {
        $minionIds = $this->getConfig('minion_ids', []);
        $count = $this->getConfig('count', 2);

        // Тут потрібно додати логіку спавну монстрів у поточну локацію
        // Це залежить від вашої системи spawn'у

        $context->addLog(sprintf(
            '<p><b class="color-summon">👥 %s вызывает %d помощников!</b></p>',
            $context->getLocationMonster()->monster->name,
            $count
        ));

        $this->markAsTriggered($context);
    }

    public function getDescription(): string
    {
        $count = $this->getConfig('count', 2);
        return "Босс вызвал {$count} юнитов в помощь";
    }
}
