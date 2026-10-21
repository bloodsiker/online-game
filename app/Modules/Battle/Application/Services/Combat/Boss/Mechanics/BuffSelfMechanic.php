<?php

namespace App\Modules\Battle\Application\Services\Combat\Boss\Mechanics;

use App\Modules\Battle\Application\Services\BattleLogTranslator;
use App\Modules\Battle\Application\Services\Combat\Boss\BossFightContext;

/**
 * Усиление - в отличие от Enrage/Berserk (только атака, оба жертвуют
 * чем-то или привязаны к низкому HP) это нейтральный самобафф: может
 * поднять атаку и/или защиту одновременно, не требует низкого HP.
 *
 * attack_increase_percent копится в том же boss_metadata['attack_modifier'],
 * что и Enrage/Berserk (см. MonsterAttackService::executeBossAttack()).
 * defense_increase_percent - отдельный ключ boss_metadata['defense_buff'],
 * гасит % входящего урона (см. AttackService::applyDefenseBuff()).
 */
class BuffSelfMechanic extends BaseBossMechanic
{
    public function execute(BossFightContext $context): void
    {
        $attackIncrease = (int) $this->getConfig('attack_increase_percent', 0);
        $defenseIncrease = (int) $this->getConfig('defense_increase_percent', 0);
        $duration = (int) $this->getConfig('duration_turns', 5);
        $isPermanent = (bool) $this->getConfig('permanent', false);

        $battle = $context->getBattle();
        $metadata = $battle->boss_metadata ?? [];

        if ($attackIncrease > 0) {
            $metadata['attack_modifier'] = ($metadata['attack_modifier'] ?? 0) + $attackIncrease;
        }

        if ($defenseIncrease > 0) {
            $metadata['defense_buff'] = [
                'percent' => $defenseIncrease,
                'created_at_turn' => $context->getCurrentTurn(),
                'expires_at_turn' => $isPermanent ? PHP_INT_MAX : $context->getCurrentTurn() + $duration,
            ];
        }

        $battle->boss_metadata = $metadata;
        $battle->save();

        $context->addLog(BattleLogTranslator::format(
            '<p><b class="color-buff">⬆️ %s усиливается!</b></p>',
            BattleLogTranslator::contentName($context->getLocationMonster()->monster)
        ));

        if ($attackIncrease > 0) {
            $context->addLog(BattleLogTranslator::format('<p class="color-stat-change">⬆️ Атака: +%d%%</p>', $attackIncrease));
        }

        if ($defenseIncrease > 0) {
            $context->addLog(BattleLogTranslator::format('<p class="color-stat-change">⬆️ Защита: блокирует %d%% урона (%s)</p>', $defenseIncrease, $isPermanent ? 'до конца боя' : "{$duration} ход(а)"));
        }

        $this->markAsTriggered($context);
    }

    public function getDescription(): string
    {
        $attackIncrease = $this->getConfig('attack_increase_percent', 0);
        $defenseIncrease = $this->getConfig('defense_increase_percent', 0);

        return "Босс усиливается (+{$attackIncrease}% атаки, +{$defenseIncrease}% защиты)";
    }
}
