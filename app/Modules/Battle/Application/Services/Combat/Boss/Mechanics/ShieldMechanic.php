<?php

namespace App\Modules\Battle\Application\Services\Combat\Boss\Mechanics;

use App\Modules\Battle\Application\Services\Combat\Boss\BossFightContext;

/**
 * Щит - імунітет до урону
 */
class ShieldMechanic extends BaseBossMechanic
{
    public function execute(BossFightContext $context): void
    {
        $shieldHp = $this->getConfig('shield_hp', 1000);
        $duration = $this->getConfig('duration_turns', 3);

        $battle = $context->getBattle();
        $metadata = $battle->boss_metadata ?? [];

        // Зберігаємо щит в метаданих
        $metadata['shield'] = [
            'hp' => $shieldHp,
            'max_hp' => $shieldHp,
            'duration' => $duration,
            'created_at_turn' => $context->getCurrentTurn(),
            'expires_at_turn' => $context->getCurrentTurn() + $duration,
        ];

        $battle->boss_metadata = $metadata;
        $battle->save();

        $context->addLog(sprintf(
            '<p><b class="color-shield">🛡️ %s создает щит на %d HP (продолжительность: %d ходов)!</b></p>',
            $context->getLocationMonster()->monster->name,
            $shieldHp,
            $duration
        ));

        $this->markAsTriggered($context);
    }

    public function getDescription(): string
    {
        $hp = $this->getConfig('shield_hp', 1000);
        $duration = $this->getConfig('duration_turns', 3);

        return "Босс создал щит на {$hp} HP ({$duration} ходов)";
    }
}
