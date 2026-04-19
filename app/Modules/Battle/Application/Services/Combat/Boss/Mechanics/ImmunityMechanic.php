<?php

namespace App\Modules\Battle\Application\Services\Combat\Boss\Mechanics;

use App\Modules\Battle\Application\Services\Combat\Boss\BossFightContext;

/**
 * Імунітет - бос повністю імунний до урону
 */
class ImmunityMechanic extends BaseBossMechanic
{
    public function execute(BossFightContext $context): void
    {
        $duration = $this->getConfig('duration_turns', 2);
        $immunityType = $this->getConfig('immunity_type', 'all'); // 'all', 'physical', 'magic'

        $battle = $context->getBattle();
        $metadata = $battle->boss_metadata ?? [];

        // Зберігаємо імунітет в метаданих
        $metadata['immunity'] = [
            'type' => $immunityType,
            'created_at_turn' => $context->getCurrentTurn(),
            'expires_at_turn' => $context->getCurrentTurn() + $duration,
            'blocked_damage' => 0,
        ];

        $battle->boss_metadata = $metadata;
        $battle->save();

        $immunityText = match ($immunityType) {
            'physical' => 'физического урона',
            'magic' => 'магического урона',
            default => 'всего урона'
        };

        $context->addLog(sprintf(
            '<p><b class="color-immunity">✨ %s становится иммунным к %s в течение %d ходов!</b></p>',
            $context->getLocationMonster()->monster->name,
            $immunityText,
            $duration
        ));

        $this->markAsTriggered($context);
    }

    public function getDescription(): string
    {
        $duration = $this->getConfig('duration_turns', 2);
        $type = $this->getConfig('immunity_type', 'all');

        $typeText = match ($type) {
            'physical' => 'физического урона',
            'magic' => 'магического урона',
            default => 'всего урона'
        };

        return "Босс стал иммунным к {$typeText} ({$duration} ходов)";
    }
}
