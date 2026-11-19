<?php

declare(strict_types=1);

namespace App\Modules\Effect\Domain\Services;

use App\Modules\Player\Infrastructure\Persistence\Models\PlayerActiveEffect;
use App\Modules\Quest\Domain\Enums\QuestPlayerStatus;
use Illuminate\Support\Facades\DB;

/**
 * Небоевые бонусы активных эффектов игрока (эликсиры Ордена Охотников).
 *
 * Хранятся в effects.stat_modifiers отдельными типами — движок статов
 * их пропускает (PlayerStatService::applyModifiers игнорирует неизвестные ключи):
 *   {"type": "aggression_reduction_percent", "value": 50, "feat_quest_id": 123, "feat_value": 100}
 *
 * Если у записи есть feat_quest_id и игрок выполнил этот квест, вместо value
 * берётся feat_value. Одинаковые бонусы не складываются — действует максимальный.
 */
class EffectBonusService
{
    public const AGGRESSION_REDUCTION = 'aggression_reduction_percent';

    public const MONSTER_EXPERIENCE = 'monster_experience_percent';

    public const DROP_CHANCE = 'drop_chance_percent';

    private const TYPES = [self::AGGRESSION_REDUCTION, self::MONSTER_EXPERIENCE, self::DROP_CHANCE];

    /** @var array<int, array<string, float>> */
    private array $memo = [];

    public function percent(int $playerId, string $type): float
    {
        return ($this->memo[$playerId] ??= $this->load($playerId))[$type] ?? 0.0;
    }

    /** Множитель шанса нападения монстра: 1.0 — без эффекта, 0.0 — монстры не нападают. */
    public function aggressionFactor(int $playerId): float
    {
        return 1 - min(100.0, max(0.0, $this->percent($playerId, self::AGGRESSION_REDUCTION))) / 100;
    }

    /** @return array<string, float> */
    private function load(int $playerId): array
    {
        $effects = PlayerActiveEffect::query()
            ->where('player_id', $playerId)
            ->whereNotNull('effect_id')
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->with('effect')
            ->get();

        $entries = [];
        foreach ($effects as $active) {
            foreach ((array) ($active->effect?->stat_modifiers ?? []) as $entry) {
                if (is_array($entry) && in_array($entry['type'] ?? null, self::TYPES, true)) {
                    $entries[] = $entry;
                }
            }
        }

        if ($entries === []) {
            return [];
        }

        $featQuestIds = array_values(array_unique(array_filter(array_map(
            static fn (array $entry): int => (int) ($entry['feat_quest_id'] ?? 0),
            $entries,
        ))));
        $completed = $featQuestIds === [] ? [] : array_flip(DB::table('quest_players')
            ->where('player_id', $playerId)
            ->whereIn('quest_id', $featQuestIds)
            ->where('status', QuestPlayerStatus::COMPLETED->value)
            ->pluck('quest_id')
            ->map(static fn ($id): int => (int) $id)
            ->all());

        $bonuses = [];
        foreach ($entries as $entry) {
            $value = (float) ($entry['value'] ?? 0);
            $featQuestId = (int) ($entry['feat_quest_id'] ?? 0);
            if ($featQuestId > 0 && isset($completed[$featQuestId], $entry['feat_value'])) {
                $value = (float) $entry['feat_value'];
            }

            $bonuses[$entry['type']] = max($bonuses[$entry['type']] ?? 0.0, $value);
        }

        return $bonuses;
    }
}
