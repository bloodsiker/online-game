<?php

declare(strict_types=1);

namespace App\Modules\Influence\Domain\Services;

use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Influence\Application\DTOs\MapInfluenceAwardResult;
use App\Modules\Influence\Domain\Enums\InfluenceRewardType;
use App\Modules\Influence\Domain\Events\MapInfluenceAwarded;
use App\Modules\Influence\Infrastructure\Persistence\Models\MapInfluence;
use App\Modules\Influence\Infrastructure\Persistence\Models\MapInfluenceLevel;
use App\Modules\Influence\Infrastructure\Persistence\Models\MapInfluenceLevelReward;
use App\Modules\Influence\Infrastructure\Persistence\Models\MapInfluenceTransaction;
use App\Modules\Influence\Infrastructure\Persistence\Models\PlayerInfluenceMedal;
use App\Modules\Location\Infrastructure\Persistence\Models\Map;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

final class MapInfluenceService
{
    public function __construct(
        private readonly BackpackService $backpackService,
        private readonly PlayerStatService $playerStatService,
        private readonly MapInfluenceBonusService $bonusService,
        private readonly MapInfluenceRequirementService $requirementService,
    ) {}

    /** @param array<string, mixed> $metadata */
    public function award(
        User $user,
        Map|int $map,
        int $amount,
        string $sourceType,
        ?int $sourceId = null,
        ?string $idempotencyKey = null,
        ?string $description = null,
        array $metadata = [],
    ): MapInfluenceAwardResult {
        if ($amount <= 0) {
            return new MapInfluenceAwardResult(0, $this->total($user->id, $map));
        }

        $mapId = $map instanceof Map ? (int) $map->id : $map;

        return DB::transaction(function () use ($user, $map, $mapId, $amount, $sourceType, $sourceId, $idempotencyKey, $description, $metadata): MapInfluenceAwardResult {
            if ($idempotencyKey !== null) {
                $existing = MapInfluenceTransaction::query()->where('idempotency_key', $idempotencyKey)->first();
                if ($existing !== null) {
                    return new MapInfluenceAwardResult(0, $this->total($user->id, $mapId));
                }
            }

            MapInfluence::query()->insertOrIgnore([
                'map_id' => $mapId,
                'user_id' => $user->id,
                'influence' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $influence = MapInfluence::query()
                ->where('map_id', $mapId)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->firstOrFail();
            $before = (int) $influence->influence;
            $after = $before + $amount;

            MapInfluenceTransaction::query()->create([
                'user_id' => $user->id,
                'map_id' => $mapId,
                'amount' => $amount,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'idempotency_key' => $idempotencyKey,
                'description' => $description,
                'metadata' => $metadata === [] ? null : $metadata,
            ]);

            $influence->forceFill(['influence' => $after])->save();

            $levels = MapInfluenceLevel::query()
                ->with(['medal', 'rewards.item'])
                ->where('map_id', $mapId)
                ->where('is_active', true)
                ->where('required_influence', '<=', $after)
                ->orderBy('required_influence')
                ->get();

            foreach ($levels as $level) {
                $this->grantLevel($user, $level);
            }

            $this->bonusService->forget($user->id, $mapId);
            $this->requirementService->forget($user->id, $mapId);
            $this->playerStatService->invalidate($user->player_id);

            MapInfluenceAwarded::dispatch(
                user: $user,
                mapName: $map instanceof Map
                    ? $map->name
                    : (string) (Map::query()->whereKey($mapId)->value('name') ?? "Карта #{$mapId}"),
                amount: $amount,
                total: $after,
            );

            return new MapInfluenceAwardResult(
                awarded: $amount,
                total: $after,
                unlockedLevels: $levels
                    ->where('required_influence', '>', $before)
                    ->pluck('name')
                    ->all(),
            );
        });
    }

    public function total(int $userId, Map|int $map): int
    {
        $mapId = $map instanceof Map ? (int) $map->id : $map;

        return (int) MapInfluence::query()
            ->where('user_id', $userId)
            ->where('map_id', $mapId)
            ->value('influence');
    }

    private function grantLevel(User $user, MapInfluenceLevel $level): void
    {
        if ($level->medal !== null) {
            PlayerInfluenceMedal::query()->firstOrCreate(
                ['user_id' => $user->id, 'influence_medal_id' => $level->medal->id],
                ['earned_at' => now()],
            );
        }

        foreach ($level->rewards as $reward) {
            $grant = DB::table('player_map_influence_level_rewards')->where([
                'user_id' => $user->id,
                'map_influence_level_reward_id' => $reward->id,
            ])->exists();
            if ($grant) {
                continue;
            }

            $this->grantReward($user, $reward);
            DB::table('player_map_influence_level_rewards')->insert([
                'user_id' => $user->id,
                'map_influence_level_reward_id' => $reward->id,
                'granted_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function grantReward(User $user, MapInfluenceLevelReward $reward): void
    {
        match ($reward->reward_type) {
            InfluenceRewardType::MONEY => $user->increment('money', $reward->amount),
            InfluenceRewardType::DIAMOND => $user->increment('diamond', $reward->amount),
            InfluenceRewardType::EXPERIENCE => $user->player()->increment('exp', $reward->amount),
            InfluenceRewardType::ITEM => $reward->item !== null
                ? $this->backpackService->addItemByShareItem($user, $reward->item, max(1, $reward->amount))
                : null,
        };
    }
}
