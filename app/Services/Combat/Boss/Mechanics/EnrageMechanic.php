<?php

namespace App\Services\Combat\Boss\Mechanics;

use App\Services\Combat\Boss\BossFightContext;

/**
 * Ярость - увеличение урона при низком HP
 */
class EnrageMechanic extends BaseBossMechanic
{
    public function execute(BossFightContext $context): void
    {
        $damageIncrease = $this->getConfig('damage_increase_percent', 50);

        $locationMonster = $context->getLocationMonster();
        $monster = $locationMonster->monster;

        // Зберігаємо модифікатор для атаки
        $metadata = $context->getBattle()->boss_metadata ?? [];
        $metadata['attack_modifier'] = ($metadata['attack_modifier'] ?? 0) + $damageIncrease;
        $context->getBattle()->boss_metadata = $metadata;
        $context->getBattle()->save();

        $context->addLog(sprintf(
            '<p><b class="color-enrage">💢 %s впадает в ярость! Урон увеличен на %d%%!</b></p>',
            $monster->name,
            $damageIncrease
        ));

        $this->markAsTriggered($context);
    }

    public function getDescription(): string
    {
        $increase = $this->getConfig('damage_increase_percent', 50);
        return "Босс разозлился и увеличил урон на {$increase}%";
    }
}
