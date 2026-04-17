<?php

namespace App\Services\Combat\Boss\Mechanics;

use App\Services\Combat\Boss\BossFightContext;

/**
 * Відбиття урону - частина отриманого урону повертається гравцю
 */
class ReflectDamageMechanic extends BaseBossMechanic
{
    public function execute(BossFightContext $context): void
    {
        $reflectPercent = $this->getConfig('reflect_percent', 30);
        $duration = $this->getConfig('duration_turns', 3);

        $battle = $context->getBattle();
        $metadata = $battle->boss_metadata ?? [];

        // Зберігаємо відбиття в метаданих
        $metadata['reflect_damage'] = [
            'percent' => $reflectPercent,
            'created_at_turn' => $context->getCurrentTurn(),
            'expires_at_turn' => $context->getCurrentTurn() + $duration,
            'total_reflected' => 0,
        ];

        $battle->boss_metadata = $metadata;
        $battle->save();

        $context->addLog(sprintf(
            '<p><b class="color-reflect">🔁 %s активирует отражение урона! %d%% урон будет возвращен вам в течение %d ходов!</b></p>',
            $context->getLocationMonster()->monster->name,
            $reflectPercent,
            $duration
        ));

        $this->markAsTriggered($context);
    }

    public function getDescription(): string
    {
        $percent = $this->getConfig('reflect_percent', 30);

        return "Босс отбивает {$percent}% урона назад";
    }
}
