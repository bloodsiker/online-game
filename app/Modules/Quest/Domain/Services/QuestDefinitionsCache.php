<?php

declare(strict_types=1);

namespace App\Modules\Quest\Domain\Services;

use App\Modules\Quest\Infrastructure\Persistence\Models\Quest;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestObjective;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Версионный кэш статичных определений квестов (меняются только админкой).
 *
 * Инвалидация — обсерверы моделей Quest / QuestStage / QuestObjective /
 * QuestReward / QuestDialogue (см. QuestDefinitionObserver): любое изменение
 * контента поднимает версию, старые записи вытесняются TTL.
 */
class QuestDefinitionsCache
{
    /** @var int Время жизни записей кэша в минутах */
    public const TTL_MINUTES = 1440;

    private const VERSION_KEY = 'quests:defs:version';

    private const AVAILABLE_TTL_MINUTES = 1440;

    private const OBJECTIVES_TTL_MINUTES = 1440;

    public static function flush(): void
    {
        Cache::forever(self::VERSION_KEY, self::version() + 1);
    }

    /**
     * Активные квесты НПС без динамических условий игрока —
     * фильтрация по exclude/completed/клану выполняется на вызывающей стороне.
     *
     * @return Collection<int, Quest>
     */
    public static function availableByNpc(int $npcId): Collection
    {
        return Cache::remember(
            'quests:avail:v'.self::version().':'.$npcId,
            now()->addMinutes(self::AVAILABLE_TTL_MINUTES),
            fn (): Collection => Quest::query()
                ->isActive()
                ->where('start_npc_id', $npcId)
                ->where('type', '!=', 'reputation')
                ->get(),
        );
    }

    /**
     * Цели квестов, сгруппированные по quest_id с ключом по id цели:
     * $map[quest_id][objective_id] => QuestObjective.
     * Горячий путь убийства монстров: вместо выборки quest_objectives
     * на каждый килл берём из кэша.
     *
     * @return array<int, array<int, QuestObjective>>
     */
    public static function objectivesByQuest(): array
    {
        return Cache::remember(
            'quests:obj:v'.self::version(),
            now()->addMinutes(self::OBJECTIVES_TTL_MINUTES),
            function (): array {
                /** @var array<int, array<int, QuestObjective>> $grouped */
                $grouped = QuestObjective::query()
                    ->get()
                    ->groupBy('quest_id')
                    ->map(fn ($group) => $group->keyBy('id'))
                    ->all();

                return $grouped;
            },
        );
    }

    private static function version(): int
    {
        return (int) Cache::get(self::VERSION_KEY, 1);
    }
}
