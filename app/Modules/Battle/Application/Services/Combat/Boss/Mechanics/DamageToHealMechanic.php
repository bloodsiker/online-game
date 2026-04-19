<?php

namespace App\Modules\Battle\Application\Services\Combat\Boss\Mechanics;

use App\Modules\Battle\Application\Services\Combat\Boss\BossFightContext;

/**
 * Конвертация урона в лечение - урон по боссу лечит его вместо повреждения
 */
class DamageToHealMechanic extends BaseBossMechanic
{
    public function execute(BossFightContext $context): void
    {
        $conversionPercent = $this->getConfig('conversion_percent', 100);
        $duration = $this->getConfig('duration_turns', 3);
        $maxHealPerHit = $this->getConfig('max_heal_per_hit');

        $battle = $context->getBattle();
        $metadata = $battle->boss_metadata ?? [];

        // Зберігаємо конвертацію в метаданих
        $metadata['damage_to_heal'] = [
            'conversion_percent' => $conversionPercent,
            'created_at_turn' => $context->getCurrentTurn(),
            'expires_at_turn' => $context->getCurrentTurn() + $duration,
            'max_heal_per_hit' => $maxHealPerHit,
            'total_healed' => 0,
            'hits_converted' => 0,
        ];

        $battle->boss_metadata = $metadata;
        $battle->save();

        $conversionText = $conversionPercent === 100
            ? 'Весь урон'
            : "{$conversionPercent}% урону";

        $context->addLog(sprintf(
            '<p><b class="color-damage-to-heal">💉 %s активирует конвертацию урона в лечение!</b></p>',
            $context->getLocationMonster()->monster->name
        ));

        $context->addLog(sprintf(
            '<p class="color-info">%s будет превращаться в здоровье в течение %d ходов!</p>',
            $conversionText,
            $duration
        ));

        if ($maxHealPerHit) {
            $context->addLog(sprintf(
                '<p class="color-info">Максимум за один хит: %d HP</p>',
                $maxHealPerHit
            ));
        }

        $this->markAsTriggered($context);
    }

    public function getDescription(): string
    {
        $percent = $this->getConfig('conversion_percent', 100);
        $duration = $this->getConfig('duration_turns', 3);

        return "Босс конвертирует {$percent}% ущерб в лечении ({$duration} ходов)";
    }
}
