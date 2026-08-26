<?php

declare(strict_types=1);

namespace App\Modules\Quest\Domain\Services;

use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Quest\Domain\Enums\QuestPlayerStatus;
use App\Modules\Quest\Domain\Events\QuestItemDropped;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestClanObjective;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestClanProgress;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestObjective;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestPlayerObjective;
use Illuminate\Support\Collection;

class QuestProgressService
{
    public function __construct(
        private readonly BackpackService $backpackService,
    ) {}

    /**
     * @return list<string> One-time messages for the caller to display.
     */
    public function progressKillAndCollect(Player $player, MonsterOnLocation $locationMonster): array
    {
        $messages = [];

        // Карта локации нужна для целей с фильтром по map_id —
        // грузим связь один раз вместо ленивой загрузки на каждую цель.
        $locationMonster->loadMissing('location');
        $locationMapId = $locationMonster->location?->map_id;

        [$questPlayers, $progressByQuestPlayer, $objectivesMap] = $this->loadPersonalProgress($player);

        foreach ($questPlayers as $questPlayer) {
            foreach ($progressByQuestPlayer[$questPlayer->id] ?? [] as $playerObj) {
                /** @var QuestObjective|null $qo */
                $qo = $objectivesMap[$questPlayer->quest_id][$playerObj->quest_objective_id] ?? null;

                if (! in_array($qo?->type, ['kill', 'collect'], true)) {
                    continue;
                }

                if ($questPlayer->current_stage_id !== null && $qo->stage_id !== $questPlayer->current_stage_id) {
                    continue;
                }

                if (! $qo->matchesMonster((int) $locationMonster->monster_id)) {
                    continue;
                }

                if ($qo->map_id && (int) $qo->map_id !== (int) $locationMapId) {
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

                // Условный инкремент закрывает гонку параллельных киллов
                // (без него двое могли бы перевалить required_amount).
                $updated = (bool) $playerObj->newQuery()
                    ->whereKey($playerObj->getKey())
                    ->where('amount', '<', $qo->required_amount)
                    ->increment('amount');

                if (! $updated) {
                    continue;
                }

                $playerObj->amount++;

                if ($qo->type === 'collect' && $qo->share_item_id) {
                    $shareItem = $qo->collectItem;
                    if ($shareItem) {
                        $this->backpackService->addItemByShareItem($player->user, $shareItem, 1);
                        QuestItemDropped::dispatch($player->user, (int) $shareItem->id);
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

                $messages[] = $msg;
            }
        }

        return $this->progressClanKillAndCollect($player, $locationMonster, $locationMapId, $messages);
    }

    private function progressClanKillAndCollect(
        Player $player,
        MonsterOnLocation $locationMonster,
        ?int $locationMapId,
        array $messages,
    ): array {
        $clanMembership = $player->user->clanMembership;
        if (! $clanMembership) {
            return $messages;
        }

        $clanProgress = QuestClanProgress::query()
            ->setEagerLoads([])
            ->where('clan_id', $clanMembership->clan_id)
            ->where('user_id', $player->user_id)
            ->where('status', QuestPlayerStatus::IN_PROGRESS)
            ->first();

        if (! $clanProgress) {
            return $messages;
        }

        $objectivesMap = QuestDefinitionsCache::objectivesByQuest();
        $clanObjectives = QuestClanObjective::query()
            ->where('quest_clan_progress_id', $clanProgress->id)
            ->get();

        foreach ($clanObjectives as $clanObj) {
            /** @var QuestObjective|null $qo */
            $qo = $objectivesMap[$clanProgress->quest_id][$clanObj->quest_objective_id] ?? null;

            if (! in_array($qo?->type, ['kill', 'collect'], true)) {
                continue;
            }

            if ($clanProgress->current_stage_id !== null && $qo->stage_id !== $clanProgress->current_stage_id) {
                continue;
            }

            if ((int) $qo->target_id !== (int) $locationMonster->monster_id) {
                continue;
            }

            if ($qo->map_id && (int) $qo->map_id !== (int) $locationMapId) {
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

            // Условный инкремент против гонки параллельных киллов (см. выше).
            $updated = (bool) $clanObj->newQuery()
                ->whereKey($clanObj->getKey())
                ->where('amount', '<', $qo->required_amount)
                ->increment('amount');

            if (! $updated) {
                continue;
            }

            $clanObj->amount++;

            if ($qo->type === 'collect' && $qo->share_item_id) {
                $shareItem = $qo->collectItem;
                if ($shareItem) {
                    $this->backpackService->addItemByShareItem($player->user, $shareItem, 1);
                    QuestItemDropped::dispatch($player->user, (int) $shareItem->id);
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

            $messages[] = $msg;
        }

        return $messages;
    }

    /**
     * Активные персональные квесты игрока + строки прогресса одним запросом.
     * Определения целей берутся из версионного кэша — SQL по статичной
     * таблице quest_objectives не выполняется.
     *
     * @return array{0: Collection<int, QuestPlayer>, 1: array<int, list<QuestPlayerObjective>>, 2: array<int, Collection<int, QuestObjective>>}
     */
    private function loadPersonalProgress(Player $player): array
    {
        $questPlayers = $player->questsInProgress()->setEagerLoads([])->get();
        if ($questPlayers->isEmpty()) {
            return [$questPlayers, [], []];
        }

        $objectivesMap = QuestDefinitionsCache::objectivesByQuest();

        $progressByQuestPlayer = QuestPlayerObjective::query()
            ->whereIn('quest_player_id', $questPlayers->modelKeys())
            ->get()
            ->groupBy('quest_player_id')
            ->all();

        return [$questPlayers, $progressByQuestPlayer, $objectivesMap];
    }

    /**
     * Откатывает прогресс сбора для 'collect'-заданий при выбросе квестового
     * предмета из рюкзака — иначе прогресс останется засчитан без предмета на руках.
     */
    public function decreaseCollectProgress(Player $player, int $shareItemId, int $qty): void
    {
        if ($qty <= 0) {
            return;
        }

        [$questPlayers, $progressByQuestPlayer, $objectivesMap] = $this->loadPersonalProgress($player);

        foreach ($questPlayers as $questPlayer) {
            foreach ($progressByQuestPlayer[$questPlayer->id] ?? [] as $playerObj) {
                /** @var QuestObjective|null $qo */
                $qo = $objectivesMap[$questPlayer->quest_id][$playerObj->quest_objective_id] ?? null;

                if (! $qo || $qo->type !== 'collect' || (int) $qo->share_item_id !== $shareItemId) {
                    continue;
                }

                $decrease = min($qty, $playerObj->amount);
                if ($decrease > 0) {
                    $playerObj->decrement('amount', $decrease);
                }
            }
        }

        $clanMembership = $player->user->clanMembership;
        if (! $clanMembership) {
            return;
        }

        $clanProgress = QuestClanProgress::query()
            ->setEagerLoads([])
            ->where('clan_id', $clanMembership->clan_id)
            ->where('user_id', $player->user_id)
            ->where('status', QuestPlayerStatus::IN_PROGRESS)
            ->first();

        if (! $clanProgress) {
            return;
        }

        $clanObjectives = QuestClanObjective::query()
            ->where('quest_clan_progress_id', $clanProgress->id)
            ->get();

        foreach ($clanObjectives as $clanObj) {
            /** @var QuestObjective|null $qo */
            $qo = $objectivesMap[$clanProgress->quest_id][$clanObj->quest_objective_id] ?? null;

            if (! $qo || $qo->type !== 'collect' || (int) $qo->share_item_id !== $shareItemId) {
                continue;
            }

            $decrease = min($qty, $clanObj->amount);
            if ($decrease > 0) {
                $clanObj->decrement('amount', $decrease);
            }
        }
    }
}
