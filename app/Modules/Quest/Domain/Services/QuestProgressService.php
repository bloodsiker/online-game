<?php

declare(strict_types=1);

namespace App\Modules\Quest\Domain\Services;

use App\DTO\AttackResultDTO;
use App\Enums\QuestPlayerStatus;
use App\Models\Monster\MonsterOnLocation;
use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestClanProgress;

class QuestProgressService
{
    public function __construct(
        private readonly BackpackService $backpackService,
    ) {}

    public function progressKillAndCollect(Player $player, MonsterOnLocation $locationMonster, AttackResultDTO $result): void
    {
        foreach ($player->questsInProgress()->with('objectives.questObjective')->get() as $questPlayer) {
            foreach ($questPlayer->objectives as $playerObj) {
                $qo = $playerObj->questObjective;

                if (! in_array($qo->type, ['kill', 'collect'])) {
                    continue;
                }

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
                        ? sprintf("<p style='margin:2px 0;'><span style='background:#d4edda; border-left:3px solid #28a745; padding:2px 6px; display:inline-block;'> <b style='color:#155724;'>%s</b> получен! Осталось собрать: <b>%s</b> <span style='color:#666;'>(с %s)</span></span></p>", $itemName, $remaining, $locationMonster->monster->name)
                        : sprintf("<p style='margin:2px 0;'><span style='background:#c3e6cb; border-left:3px solid #28a745; padding:2px 6px; display:inline-block;'>✅ <b style='color:#155724;'>%s</b> — все собраны для квеста!</span></p>", $itemName);
                } else {
                    $monsterName = $locationMonster->monster->name;
                    $msg = $remaining > 0
                        ? sprintf("<p style='margin:2px 0;'><span style='background:#fde8e8; border-left:3px solid #c0392b; padding:2px 6px; display:inline-block;'>⚔️ <b style='color:#7b1a1a;'>%s</b> уничтожен! Осталось убить: <b>%s</b></span></p>", $monsterName, $remaining)
                        : sprintf("<p style='margin:2px 0;'><span style='background:#f5c6c6; border-left:3px solid #c0392b; padding:2px 6px; display:inline-block;'>✅ <b style='color:#7b1a1a;'>%s</b> — все уничтожены для квеста!</span></p>", $monsterName);
                }

                $result->log($msg);
            }
        }

        $clanMembership = $player->user->clanMembership;
        if (! $clanMembership) {
            return;
        }

        $clanProgress = QuestClanProgress::where('clan_id', $clanMembership->clan_id)
            ->where('user_id', $player->user_id)
            ->where('status', QuestPlayerStatus::IN_PROGRESS)
            ->with('objectives.questObjective')
            ->first();

        if (! $clanProgress) {
            return;
        }

        foreach ($clanProgress->objectives as $clanObj) {
            $qo = $clanObj->questObjective;

            if (! in_array($qo->type, ['kill', 'collect'])) {
                continue;
            }

            if ($clanProgress->current_stage_id !== null && $qo->stage_id !== $clanProgress->current_stage_id) {
                continue;
            }

            if ((int) $qo->target_id !== (int) $locationMonster->monster_id) {
                continue;
            }

            if ($qo->map_id && (int) $qo->map_id !== (int) $locationMonster->location->map_id) {
                continue;
            }

            if ($clanObj->amount >= $qo->required_amount) {
                continue;
            }

            if ($qo->type === 'collect' && $qo->drop_chance !== null) {
                $roll = mt_rand(0, 10000) / 100;
                if ($roll > $qo->drop_chance) {
                    continue;
                }
            }

            $clanObj->increment('amount');
            $clanObj->refresh();

            if ($qo->type === 'collect' && $qo->share_item_id) {
                $shareItem = $qo->collectItem;
                if ($shareItem) {
                    $this->backpackService->addItemByShareItem($player->user, $shareItem, 1);
                }
            }

            $remaining = $qo->required_amount - $clanObj->amount;

            if ($qo->type === 'collect') {
                $itemName = $qo->collectItem?->name ?? $qo->description ?? 'предмет';
                $msg = $remaining > 0
                    ? sprintf("<p style='margin:2px 0;'><span style='background:#fff3cd; border-left:3px solid #c8990a; padding:2px 6px; display:inline-block;'>[Клан] <b style='color:#5a3e00;'>%s</b> получен! Осталось: <b>%s</b></span></p>", $itemName, $remaining)
                    : sprintf("<p style='margin:2px 0;'><span style='background:#fff3cd; border-left:3px solid #c8990a; padding:2px 6px; display:inline-block;'>✅ [Клан] <b style='color:#5a3e00;'>%s</b> — все собраны!</span></p>", $itemName);
            } else {
                $monsterName = $locationMonster->monster->name;
                $msg = $remaining > 0
                    ? sprintf("<p style='margin:2px 0;'><span style='background:#fff3cd; border-left:3px solid #c8990a; padding:2px 6px; display:inline-block;'>[Клан] ⚔️ <b style='color:#5a3e00;'>%s</b> уничтожен! Осталось: <b>%s</b></span></p>", $monsterName, $remaining)
                    : sprintf("<p style='margin:2px 0;'><span style='background:#fff3cd; border-left:3px solid #c8990a; padding:2px 6px; display:inline-block;'>✅ 🏰 [Клан] <b style='color:#5a3e00;'>%s</b> — все уничтожены!</span></p>", $monsterName);
            }

            $result->log($msg);
        }
    }
}
