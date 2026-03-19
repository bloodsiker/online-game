<?php

namespace App\Services;

use App\DTO\AttackResultDTO;
use App\Models\Monster\MonsterOnLocation;
use App\Models\Player\Player;

class QuestProgressService
{
    public function __construct(private readonly BackpackService $backpackService) {}

    public function progressKillAndCollect(Player $player, MonsterOnLocation $locationMonster, AttackResultDTO $result): void
    {
        foreach ($player->questsInProgress()->with('objectives.questObjective')->get() as $questPlayer) {
            foreach ($questPlayer->objectives as $playerObj) {
                $qo = $playerObj->questObjective;

                if (! in_array($qo->type, ['kill', 'collect'])) {
                    continue;
                }

                // For staged quests: only process objectives of the current stage
                if ($questPlayer->current_stage_id !== null && $qo->stage_id !== $questPlayer->current_stage_id) {
                    continue;
                }

                if ((int) $qo->target_id !== (int) $locationMonster->monster_id) {
                    continue;
                }

                if ($qo->map_id && (int) $qo->map_id !== (int) $locationMonster->location->map_id) {
                    continue;
                }

                if ($playerObj->amount >= $qo->required_amount) {
                    continue;
                }

                if ($qo->type === 'collect' && $qo->drop_chance !== null) {
                    $roll = mt_rand(0, 10000) / 100;
                    if ($roll > $qo->drop_chance) {
                        continue;
                    }
                }

                $playerObj->increment('amount');
                $playerObj->refresh();

                // Physically add the collect item to the player's backpack
                if ($qo->type === 'collect' && $qo->share_item_id) {
                    $shareItem = $qo->collectItem;
                    if ($shareItem) {
                        $this->backpackService->addItemByShareItem($player->user, $shareItem, 1);
                    }
                }

                $remaining = $qo->required_amount - $playerObj->amount;

                if ($qo->type === 'collect') {
                    $itemName = $qo->collectItem?->name ?? $qo->description ?? 'предмет';
                    $msg = $remaining > 0
                        ? sprintf(
                            "<p style='margin:2px 0;'><span style='background:#d4edda; border-left:3px solid #28a745; padding:2px 6px; display:inline-block;'> <b style='color:#155724;'>%s</b> получен! Осталось собрать: <b>%s</b> <span style='color:#666;'>(с %s)</span></span></p>",
                            $itemName,
                            $remaining,
                            $locationMonster->monster->name
                        )
                        : sprintf(
                            "<p style='margin:2px 0;'><span style='background:#c3e6cb; border-left:3px solid #28a745; padding:2px 6px; display:inline-block;'>✅ <b style='color:#155724;'>%s</b> — все собраны для квеста!</span></p>",
                            $itemName
                        );
                } else {
                    $monsterName = $locationMonster->monster->name;
                    $msg = $remaining > 0
                        ? sprintf(
                            "<p style='margin:2px 0;'><span style='background:#fde8e8; border-left:3px solid #c0392b; padding:2px 6px; display:inline-block;'>⚔️ <b style='color:#7b1a1a;'>%s</b> уничтожен! Осталось убить: <b>%s</b></span></p>",
                            $monsterName,
                            $remaining
                        )
                        : sprintf(
                            "<p style='margin:2px 0;'><span style='background:#f5c6c6; border-left:3px solid #c0392b; padding:2px 6px; display:inline-block;'>✅ <b style='color:#7b1a1a;'>%s</b> — все уничтожены для квеста!</span></p>",
                            $monsterName
                        );
                }

                $result->log($msg);
            }
        }
    }
}
