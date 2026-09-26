<?php

declare(strict_types=1);

namespace App\Modules\Influence\Domain\Services;

use App\Modules\Influence\Application\DTOs\MapInfluenceBonuses;
use App\Modules\Influence\Domain\Enums\InfluenceBonusType;
use App\Modules\Influence\Infrastructure\Persistence\Models\MapInfluence;
use App\Modules\Influence\Infrastructure\Persistence\Models\MapInfluenceLevel;

final class MapInfluenceBonusService
{
    /** @var array<string, MapInfluenceBonuses> */
    private array $resolved = [];

    public function for(int $userId, int $mapId): MapInfluenceBonuses
    {
        $key = $userId.':'.$mapId;
        if (isset($this->resolved[$key])) {
            return $this->resolved[$key];
        }

        $points = (int) MapInfluence::query()
            ->where('user_id', $userId)
            ->where('map_id', $mapId)
            ->value('influence');
        $level = MapInfluenceLevel::query()
            ->with('bonuses')
            ->where('map_id', $mapId)
            ->where('is_active', true)
            ->where('required_influence', '<=', $points)
            ->orderByDesc('required_influence')
            ->first();

        $values = $level?->bonuses->mapWithKeys(
            static fn ($bonus): array => [$bonus->bonus_type->value => (float) $bonus->value],
        ) ?? collect();

        return $this->resolved[$key] = new MapInfluenceBonuses(
            gatheringSpeedPercent: (float) ($values[InfluenceBonusType::GATHERING_SPEED_PERCENT->value] ?? 0),
            bonusResourceChancePercent: (float) ($values[InfluenceBonusType::BONUS_RESOURCE_CHANCE_PERCENT->value] ?? 0),
            monsterMoneyPercent: (float) ($values[InfluenceBonusType::MONSTER_MONEY_PERCENT->value] ?? 0),
        );
    }

    public function forget(int $userId, int $mapId): void
    {
        unset($this->resolved[$userId.':'.$mapId]);
    }
}
