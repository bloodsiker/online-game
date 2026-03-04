<?php

namespace App\Services\Combat\Boss\Mechanics;

use App\Services\Combat\Boss\BossFightContext;

/**
 * Берсерк - жертвує захистом заради збільшення атаки
 */
class BerserkMechanic extends BaseBossMechanic
{
    public function execute(BossFightContext $context): void
    {
        $attackIncrease = $this->getConfig('attack_increase_percent', 100);
        $defenseDecrease = $this->getConfig('defense_decrease_percent', 50);
        $duration = $this->getConfig('duration_turns', 5);
        $isPermanent = $this->getConfig('permanent', false);

        $battle = $context->getBattle();
        $metadata = $battle->boss_metadata ?? [];

        // Застосовуємо берсерк модифікатори
        $metadata['berserk'] = [
            'attack_increase' => $attackIncrease,
            'defense_decrease' => $defenseDecrease,
            'created_at_turn' => $context->getCurrentTurn(),
            'expires_at_turn' => $isPermanent ? PHP_INT_MAX : $context->getCurrentTurn() + $duration,
            'permanent' => $isPermanent,
        ];

        // Також додаємо до загального attack_modifier
        $currentModifier = $metadata['attack_modifier'] ?? 0;
        $metadata['attack_modifier'] = $currentModifier + $attackIncrease;

        $battle->boss_metadata = $metadata;
        $battle->save();

        $durationText = $isPermanent
            ? 'до конца боя'
            : "в течение {$duration} ходов";

        $context->addLog(sprintf(
            '<p><b class="color-berserk">😡 %s входит в состояние берсерка %s!</b></p>',
            $context->getLocationMonster()->monster->name,
            $durationText
        ));

        $context->addLog(sprintf(
            '<p class="color-stat-change">⬆️ Атака: +%d%%</p>',
            $attackIncrease
        ));

        $context->addLog(sprintf(
            '<p class="color-stat-change">⬇️ Защита: -%d%%</p>',
            $defenseDecrease
        ));

        $this->markAsTriggered($context);
    }

    public function getDescription(): string
    {
        $attackInc = $this->getConfig('attack_increase_percent', 100);
        $defDec = $this->getConfig('defense_decrease_percent', 50);

        return "Босс входит в берсерк (+{$attackInc}% атаки, -{$defDec}% защиты)";
    }
}
