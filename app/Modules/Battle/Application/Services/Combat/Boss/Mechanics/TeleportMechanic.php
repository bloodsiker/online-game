<?php

namespace App\Modules\Battle\Application\Services\Combat\Boss\Mechanics;

use App\Modules\Battle\Application\Services\BattleLogTranslator;
use App\Modules\Battle\Application\Services\Combat\Boss\BossFightContext;

/**
 * Телепортация - босс на несколько ходов становится "недостижим": все
 * удары игрока проходят мимо (см. AttackService::checkTeleportDodge(),
 * ключ boss_metadata['teleport_dodge']).
 */
class TeleportMechanic extends BaseBossMechanic
{
    public function execute(BossFightContext $context): void
    {
        $duration = $this->getConfig('duration_turns', 2);

        $battle = $context->getBattle();
        $metadata = $battle->boss_metadata ?? [];

        $metadata['teleport_dodge'] = [
            'created_at_turn' => $context->getCurrentTurn(),
            'expires_at_turn' => $context->getCurrentTurn() + $duration,
        ];

        $battle->boss_metadata = $metadata;
        $battle->save();

        $context->addLog(BattleLogTranslator::format(
            '<p><b class="color-teleport">🌀 %s телепортируется! Следующие %d ход(а) ваши удары будут проходить мимо!</b></p>',
            BattleLogTranslator::contentName($context->getLocationMonster()->monster),
            $duration
        ));

        $this->markAsTriggered($context);
    }

    public function getDescription(): string
    {
        $duration = $this->getConfig('duration_turns', 2);

        return "Босс телепортируется и становится недостижим для атак ({$duration} ход(а))";
    }
}
