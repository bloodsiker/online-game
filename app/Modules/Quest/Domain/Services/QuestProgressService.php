<?php

declare(strict_types=1);

namespace App\Modules\Quest\Domain\Services;

use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Quest\Domain\DTOs\QuestItemGateState;
use App\Modules\Quest\Domain\Enums\QuestPlayerStatus;
use App\Modules\Quest\Domain\Events\QuestItemDropped;
use App\Modules\Quest\Infrastructure\Persistence\Models\Quest;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestClanObjective;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestClanProgress;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestObjective;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestPlayerObjective;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;

class QuestProgressService
{
    public function __construct(
        private readonly BackpackService $backpackService,
    ) {}

    public function resolveItemGate(User $user, Quest $quest): QuestItemGateState
    {
        $objectives = $quest->objectives
            ->where('type', 'collect')
            ->where('target_type', 'item')
            ->filter(fn (QuestObjective $objective) => (bool) $objective->share_item_id);

        if ($objectives->isEmpty()) {
            return new QuestItemGateState(
                backpackCounts: [],
                missingItemNames: [],
            );
        }

        $objectives->loadMissing('collectItem');
        $counts = $this->backpackService->countByShareItemIds(
            $user,
            $objectives->pluck('share_item_id')->map(fn ($id) => (int) $id)->unique()->values()->all(),
        );

        $missing = $objectives
            ->filter(fn (QuestObjective $objective) => ($counts[(int) $objective->share_item_id] ?? 0) < (int) $objective->required_amount)
            ->map(fn (QuestObjective $objective) => $objective->collectItem?->name ?? 'предмет')
            ->values()
            ->all();

        return new QuestItemGateState(
            backpackCounts: $counts,
            missingItemNames: $missing,
        );
    }

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

    /**
     * Прогрессирует цели type=talk при посещении страницы НПС — у kill/collect триггер
     * бой, у talk триггера не было вообще: amount никогда не рос, required_amount(1)
     * был недостижим, квест с такой целью нельзя было завершить ни при каких условиях
     * (см. QuestPlayer::isAllObjectivesComplete/isCurrentStageComplete — 'talk' не входит
     * в список типов, освобождённых от проверки amount, в отличие от 'deliver').
     *
     * @return list<string> Одноразовые сообщения для отображения на странице НПС.
     */
    public function progressTalk(Player $player, int $npcId): array
    {
        $messages = [];

        [$questPlayers, $progressByQuestPlayer, $objectivesMap] = $this->loadPersonalProgress($player);

        foreach ($questPlayers as $questPlayer) {
            foreach ($progressByQuestPlayer[$questPlayer->id] ?? [] as $playerObj) {
                /** @var QuestObjective|null $qo */
                $qo = $objectivesMap[$questPlayer->quest_id][$playerObj->quest_objective_id] ?? null;

                if ($qo?->type !== 'talk' || $qo->target_type !== 'npc') {
                    continue;
                }

                if ($questPlayer->current_stage_id !== null && $qo->stage_id !== $questPlayer->current_stage_id) {
                    continue;
                }

                if (! $qo->matchesMonster($npcId)) {
                    continue;
                }

                if ($playerObj->amount >= $qo->required_amount) {
                    continue;
                }

                // Тот же race-safe условный инкремент, что и у kill/collect.
                $updated = (bool) $playerObj->newQuery()
                    ->whereKey($playerObj->getKey())
                    ->where('amount', '<', $qo->required_amount)
                    ->increment('amount');

                if (! $updated) {
                    continue;
                }

                $playerObj->amount++;

                $remaining = $qo->required_amount - $playerObj->amount;
                $messages[] = $remaining > 0
                    ? sprintf("<p style='margin:2px 0;'><span style='background:#e2e3ff; border-left:3px solid #4b3fae; padding:2px 6px; display:inline-block;'>💬 Разговор состоялся. Осталось поговорить ещё с: <b>%s</b></span></p>", $remaining)
                    : "<p style='margin:2px 0;'><span style='background:#d6d8ff; border-left:3px solid #4b3fae; padding:2px 6px; display:inline-block;'>✅ Все нужные разговоры состоялись!</span></p>";
            }
        }

        return $messages;
    }

    /**
     * Засчитывает успешное использование предмета в активных целях use_item.
     * Контекст цели вычисляется на сервере по текущей локации игрока, поэтому
     * клиент не может подменить NPC, монстра, карту или локацию.
     *
     * @return array{matched: bool, consume: bool, messages: list<string>}
     */
    public function progressItemUse(Player $player, int $shareItemId): array
    {
        return $this->progressPreparedItemUse($this->prepareItemUse($player, $shareItemId));
    }

    /**
     * Один раз на запрос находит все подходящие незавершённые цели. Подготовленный
     * список можно сначала использовать для проверки доступности предмета, а затем
     * передать в progressPreparedItemUse() без повторного обхода и запросов.
     *
     * @return list<array{progress: QuestPlayerObjective|QuestClanObjective, objective: QuestObjective}>
     */
    public function prepareItemUse(Player $player, int $shareItemId): array
    {
        $matches = [];
        $definitions = QuestDefinitionsCache::objectivesByQuest();
        $targetTypes = $this->itemUseTargetTypes($definitions, $shareItemId);
        if ($targetTypes === []) {
            return $matches;
        }

        [$questPlayers, $progressByQuestPlayer, $objectivesMap] = $this->loadPersonalProgress($player);
        $context = $this->itemUseContext($player, $targetTypes);

        foreach ($questPlayers as $questPlayer) {
            foreach ($progressByQuestPlayer[$questPlayer->id] ?? [] as $progress) {
                $objective = $objectivesMap[$questPlayer->quest_id][$progress->quest_objective_id] ?? null;
                if ($this->isPendingItemUseMatch($progress, $objective, $questPlayer->current_stage_id, $shareItemId, $context)) {
                    $matches[] = compact('progress', 'objective');
                }
            }
        }

        $membership = $player->user->clanMembership;
        if (! $membership) {
            return $matches;
        }

        $clanProgress = QuestClanProgress::query()
            ->setEagerLoads([])
            ->where('clan_id', $membership->clan_id)
            ->where('user_id', $player->user_id)
            ->where('status', QuestPlayerStatus::IN_PROGRESS)
            ->first();

        if (! $clanProgress) {
            return $matches;
        }

        foreach (QuestClanObjective::query()->where('quest_clan_progress_id', $clanProgress->id)->get() as $progress) {
            $objective = $objectivesMap[$clanProgress->quest_id][$progress->quest_objective_id] ?? null;
            if ($this->isPendingItemUseMatch($progress, $objective, $clanProgress->current_stage_id, $shareItemId, $context)) {
                $matches[] = compact('progress', 'objective');
            }
        }

        return $matches;
    }

    /**
     * @param  list<array{progress: QuestPlayerObjective|QuestClanObjective, objective: QuestObjective}>  $matches
     * @return array{matched: bool, consume: bool, messages: list<string>}
     */
    public function progressPreparedItemUse(array $matches): array
    {
        $result = ['matched' => false, 'consume' => false, 'messages' => []];

        foreach ($matches as $match) {
            $this->incrementItemUseObjective($match['progress'], $match['objective'], $result);
        }

        return $result;
    }

    /**
     * @param  list<string>  $targetTypes
     * @return array{location_id: int, map_id: ?int, npc_ids: list<int>, monster_ids: list<int>}
     */
    private function itemUseContext(Player $player, array $targetTypes): array
    {
        $locationId = (int) $player->user->location_id;

        return [
            'location_id' => $locationId,
            'map_id' => Location::query()->whereKey($locationId)->value('map_id'),
            'npc_ids' => in_array('npc', $targetTypes, true)
                ? Npc::query()->where('location_id', $locationId)->where('is_active', true)
                    ->pluck('id')->map(fn ($id) => (int) $id)->all()
                : [],
            'monster_ids' => in_array('monster', $targetTypes, true)
                ? MonsterOnLocation::query()->where('location_id', $locationId)->where('active', true)
                    ->pluck('monster_id')->map(fn ($id) => (int) $id)->unique()->values()->all()
                : [],
        ];
    }

    /**
     * @param  array<int, Collection<int, QuestObjective>>  $objectivesMap
     * @return list<string>
     */
    private function itemUseTargetTypes(array $objectivesMap, int $shareItemId): array
    {
        return collect($objectivesMap)
            ->flatten(1)
            ->filter(fn (QuestObjective $objective) => $objective->type === 'use_item'
                && (int) $objective->share_item_id === $shareItemId)
            ->pluck('target_type')
            ->unique()
            ->values()
            ->all();
    }

    /** @param array{location_id: int, map_id: ?int, npc_ids: list<int>, monster_ids: list<int>} $context */
    private function matchesItemUse(
        ?QuestObjective $objective,
        ?int $currentStageId,
        int $shareItemId,
        array $context,
    ): bool {
        if ($objective?->type !== 'use_item' || (int) $objective->share_item_id !== $shareItemId) {
            return false;
        }

        if ($currentStageId !== null && (int) $objective->stage_id !== $currentStageId) {
            return false;
        }

        if ($objective->map_id && (int) $objective->map_id !== (int) $context['map_id']) {
            return false;
        }

        return match ($objective->target_type) {
            'location' => (int) $objective->target_id === $context['location_id'],
            'npc' => in_array((int) $objective->target_id, $context['npc_ids'], true),
            'monster' => in_array((int) $objective->target_id, $context['monster_ids'], true),
            'item' => ! $objective->target_id || (int) $objective->target_id === $shareItemId,
            default => false,
        };
    }

    /**
     * @param  QuestPlayerObjective|QuestClanObjective  $progress
     * @param  array{location_id: int, map_id: ?int, npc_ids: list<int>, monster_ids: list<int>}  $context
     */
    private function isPendingItemUseMatch(
        $progress,
        ?QuestObjective $objective,
        ?int $currentStageId,
        int $shareItemId,
        array $context,
    ): bool {
        return $this->matchesItemUse($objective, $currentStageId, $shareItemId, $context)
            && (int) $progress->amount < max(1, (int) $objective->required_amount);
    }

    /**
     * @param  QuestPlayerObjective|QuestClanObjective  $progress
     * @param  array{matched: bool, consume: bool, messages: list<string>}  $result
     */
    private function incrementItemUseObjective($progress, QuestObjective $objective, array &$result): void
    {
        $required = max(1, (int) $objective->required_amount);
        if ((int) $progress->amount >= $required) {
            return;
        }

        $updated = (bool) $progress->newQuery()
            ->whereKey($progress->getKey())
            ->where('amount', '<', $required)
            ->increment('amount');

        if (! $updated) {
            return;
        }

        $progress->amount++;
        $result['matched'] = true;
        $result['consume'] = $result['consume'] || (bool) $objective->consume_item;
        $result['messages'][] = $objective->description ?: 'Квестовый предмет использован.';
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
