<?php

namespace App\Services;

use App\DTO\AttackResultDTO;
use App\Models\Monster\MonsterOnLocation;
use App\Models\Player\Player;

class QuestProgressService
{
    public function progressKillAndCollect(Player $player, MonsterOnLocation $locationMonster, AttackResultDTO $result): void
    {
        foreach ($player->questsInProgress()->with('objective.questObjective')->get() as $quest) {
            $objective = $quest->objective;
            $qo = $objective->questObjective;

            if (!in_array($qo->type, ['kill', 'collect']) || $qo->target_id !== $locationMonster->monster_id) {
                continue;
            }

            if ($objective->amount < $qo->required_amount) {
                $objective->increment('amount');
                $objective->save();

                $needAmount = $qo->required_amount - $objective->amount;
                $msg = $needAmount > 0
                    ? sprintf("<p><span style='background:#ccf292;'>Осталось убить <b>%s %s</b></span></p>", $needAmount, $locationMonster->monster->name)
                    : sprintf("<p><span style='background:#ccf292;'><b>Вы убили нужное количество %s для квеста</b></span></p>", $objective->amount);

                $result->log($msg);
            }
        }
    }
}
