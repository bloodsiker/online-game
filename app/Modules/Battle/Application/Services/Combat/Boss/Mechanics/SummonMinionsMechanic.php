<?php

namespace App\Modules\Battle\Application\Services\Combat\Boss\Mechanics;

use App\Modules\Battle\Application\Services\Combat\Boss\BossFightContext;
use App\Modules\Battle\Domain\Enums\BattleDetailStatus;
use App\Modules\Battle\Infrastructure\Persistence\BattleRepository;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleDetail;
use App\Modules\Monster\Infrastructure\Persistence\Models\BossMechanic;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterSummonPool;
use App\Repositories\MonsterOnLocationRepository;
use Illuminate\Support\Collection;

/**
 * Вызов миньонов: реально создаёт монстров на локации и подключает их
 * к текущему бою (BattleDetail) — они становятся обычными участниками
 * общего боя (сервер каждый раунд случайно выбирает, кто из живых
 * монстров боя контратакует игрока — см. FightController).
 *
 * config:
 *   count: int — фиксированное количество за призыв
 *   min_count/max_count: int — случайный диапазон (приоритетнее count)
 *   max_active: int|null — не призывать новых, если уже живо >= этого числа
 *     миньонов, призванных ЭТОЙ механикой (null = без ограничения)
 *   cooldown_turns: int — обязателен, если механика должна призывать
 *     миньонов периодически, а не один раз за бой (см. BossMechanicsService)
 *
 * Каких мобов призывать — берётся из таблицы monster_summon_pool
 * (Monster::summonPool()), настраивается в админке отдельно от config.
 */
class SummonMinionsMechanic extends BaseBossMechanic
{
    public function __construct(
        BossMechanic $mechanic,
        private readonly MonsterOnLocationRepository $monsterOnLocationRepository,
        private readonly BattleRepository $battleRepository,
    ) {
        parent::__construct($mechanic);
    }

    public function execute(BossFightContext $context): void
    {
        $bossMonster = $context->getLocationMonster()->monster;

        $pool = MonsterSummonPool::where('monster_id', $bossMonster->id)
            ->with('minionMonster')
            ->get()
            ->filter(fn (MonsterSummonPool $entry) => $entry->minionMonster !== null);

        if ($pool->isEmpty()) {
            return;
        }

        $maxActive = $this->getConfig('max_active');
        if ($maxActive !== null && $this->countAliveSummoned($context) >= (int) $maxActive) {
            return;
        }

        $count = $this->rollCount();
        $location = $context->getLocationMonster()->location;
        $dungeonSessionId = $context->getLocationMonster()->dungeon_session_id;

        $spawnedLocationMonsterIds = [];
        $spawnedNames = [];

        for ($i = 0; $i < $count; $i++) {
            $minion = $this->pickWeighted($pool)->minionMonster;

            $spawned = $this->monsterOnLocationRepository->createMonsterOnLocation(
                $minion,
                $location,
                $dungeonSessionId,
                null,
            );

            $this->battleRepository->createBattleDetails($context->getBattle(), null, $spawned);

            $spawnedLocationMonsterIds[] = $spawned->id;
            $spawnedNames[] = $minion->name;
        }

        $this->rememberSummoned($context, $spawnedLocationMonsterIds);

        $context->addLog(sprintf(
            '<p><b class="color-summon">👥 %s призывает на помощь: %s!</b></p>',
            $bossMonster->name,
            implode(', ', $spawnedNames)
        ));

        $this->markAsTriggered($context);
    }

    public function getDescription(): string
    {
        return 'Чрево призывает подкрепление из глубин кургана';
    }

    private function rollCount(): int
    {
        $min = $this->getConfig('min_count');
        $max = $this->getConfig('max_count');

        if ($min !== null && $max !== null) {
            return random_int((int) $min, (int) $max);
        }

        return (int) $this->getConfig('count', 2);
    }

    private function pickWeighted(Collection $pool): MonsterSummonPool
    {
        $totalWeight = max(1, (int) $pool->sum('weight'));
        $roll = random_int(1, $totalWeight);
        $cumulative = 0;

        foreach ($pool as $entry) {
            $cumulative += max(1, (int) $entry->weight);
            if ($roll <= $cumulative) {
                return $entry;
            }
        }

        return $pool->last();
    }

    private function countAliveSummoned(BossFightContext $context): int
    {
        $ids = $context->getMechanicData("mechanic_{$this->mechanic->id}_summoned_ids", []);

        if (empty($ids)) {
            return 0;
        }

        return BattleDetail::where('battle_id', $context->getBattle()->id)
            ->whereIn('location_monster_id', $ids)
            ->where('status', BattleDetailStatus::LIFE)
            ->count();
    }

    private function rememberSummoned(BossFightContext $context, array $newIds): void
    {
        $existing = $context->getMechanicData("mechanic_{$this->mechanic->id}_summoned_ids", []);
        $context->setMechanicData("mechanic_{$this->mechanic->id}_summoned_ids", array_merge($existing, $newIds));
    }
}