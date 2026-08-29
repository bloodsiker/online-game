<?php

declare(strict_types=1);

namespace App\Modules\Location\Domain\Services;

use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Battle\Domain\Enums\BattleDetailStatus;
use App\Modules\Battle\Domain\Enums\BattleStatus;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleDetail;
use App\Modules\Location\Application\DTOs\GatheringActionResultDTO;
use App\Modules\Location\Infrastructure\Persistence\Models\GatheringAttempt;
use App\Modules\Location\Infrastructure\Persistence\Models\GatheringNode;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Location\Infrastructure\Persistence\Models\MapGatheringResource;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerEquipment;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerSkill;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Skill\Infrastructure\Persistence\Models\Skill;
use App\Modules\Skill\Infrastructure\Persistence\Models\SkillLevelRequirement;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class GatheringService
{
    private const PROFESSION_NAMES = ['Травник', 'Рыбак', 'Геолог'];

    public function __construct(
        private readonly BackpackService $backpackService,
    ) {}

    public function state(User $user): array
    {
        $user->loadMissing(['currentLocation.map', 'player']);
        $location = $user->currentLocation;
        $blocked = $this->areaBlockReason($user, $location);

        if ($blocked !== null) {
            $this->cancelForPlayer((int) $user->player->id);

            return [
                'enabled' => false,
                'message' => $blocked,
                'serverTime' => now()->toIso8601String(),
                'nodes' => [],
                'professions' => $this->professionStates($user->player),
                'activeAttempt' => null,
            ];
        }

        $this->deleteExpiredAttempts();
        $this->cancelIfContextChanged($user);
        $this->synchronizeNodes((int) $location->map_id);

        $activeAttempt = GatheringAttempt::query()
            ->where('player_id', $user->player->id)
            ->where('expires_at', '>', now())
            ->first();

        $nodes = GatheringNode::query()
            ->whereHas('mapResource', fn ($query) => $query->where('map_id', $location->map_id))
            ->where(fn ($query) => $query->whereNull('respawn_at')->orWhere('respawn_at', '<=', now()))
            ->with([
                'mapResource.resource.skill',
                'mapResource.resource.gatheringTool',
                'attempts' => fn ($query) => $query->where('expires_at', '>', now()),
            ])
            ->orderBy('id')
            ->get()
            ->map(fn (GatheringNode $node): array => $this->nodeState($user, $node))
            ->values()
            ->all();

        return [
            'enabled' => true,
            'message' => $nodes === [] ? 'На этой карте пока не настроены ресурсы.' : null,
            'serverTime' => now()->toIso8601String(),
            'nodes' => $nodes,
            'professions' => $this->professionStates($user->player),
            'activeAttempt' => $activeAttempt === null ? null : [
                'nodeId' => (int) $activeAttempt->gathering_node_id,
                'startedAt' => $activeAttempt->created_at->toIso8601String(),
                'completesAt' => $activeAttempt->completes_at->toIso8601String(),
                'expiresAt' => $activeAttempt->expires_at->toIso8601String(),
            ],
        ];
    }

    public function start(User $user, int $nodeId): GatheringActionResultDTO
    {
        try {
            return DB::transaction(function () use ($user, $nodeId): GatheringActionResultDTO {
                $lockedPlayer = Player::query()->whereKey($user->player->id)->lockForUpdate()->first();
                $lockedUser = User::query()->whereKey($user->id)->lockForUpdate()->first();
                if ($lockedPlayer === null || $lockedUser === null) {
                    return $this->failure('Игрок не найден.', 404);
                }

                $lockedUser->setRelation('player', $lockedPlayer);
                $lockedUser->load('currentLocation.map');
                $location = $lockedUser->currentLocation;
                $blocked = $this->areaBlockReason($lockedUser, $location);
                if ($blocked !== null) {
                    return $this->failure($blocked, 422);
                }

                GatheringAttempt::query()->where('expires_at', '<=', now())->delete();
                if (GatheringAttempt::query()->where('player_id', $lockedPlayer->id)->exists()) {
                    return $this->failure('Сначала завершите текущую добычу.', 409);
                }

                $node = GatheringNode::query()
                    ->whereKey($nodeId)
                    ->lockForUpdate()
                    ->with(['mapResource.resource.skill', 'mapResource.resource.gatheringTool'])
                    ->first();
                if ($node === null || (int) $node->mapResource->map_id !== (int) $location->map_id) {
                    return $this->failure('Ресурс не найден на этой карте.', 404);
                }

                if ($node->respawn_at !== null && $node->respawn_at->isFuture()) {
                    return $this->failure('Этот ресурс ещё не появился.', 409);
                }

                $resource = $node->mapResource->resource;
                $resourceBlock = $this->resourceBlockReason($lockedPlayer, $resource);
                if ($resourceBlock !== null) {
                    return $this->failure($resourceBlock, 422);
                }

                $seconds = max(1, (int) $resource->gathering_time_seconds);
                $completesAt = now()->addSeconds($seconds);
                $attempt = GatheringAttempt::create([
                    'player_id' => $lockedPlayer->id,
                    'gathering_node_id' => $node->id,
                    'location_id' => $location->id,
                    'completes_at' => $completesAt,
                    'expires_at' => $completesAt->copy()->addSeconds(GatheringAttempt::CLAIM_GRACE_SECONDS),
                ]);

                return new GatheringActionResultDTO(
                    ok: true,
                    message: sprintf('Вы начали добывать «%s».', $resource->name),
                    data: [
                        'attempt' => [
                            'nodeId' => (int) $node->id,
                            'startedAt' => $attempt->created_at->toIso8601String(),
                            'completesAt' => $attempt->completes_at->toIso8601String(),
                            'expiresAt' => $attempt->expires_at->toIso8601String(),
                        ],
                    ],
                );
            });
        } catch (QueryException $exception) {
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }

            return $this->failure('Этот ресурс уже занят.', 409);
        }
    }

    public function complete(User $user): GatheringActionResultDTO
    {
        return DB::transaction(function () use ($user): GatheringActionResultDTO {
            $attempt = GatheringAttempt::query()
                ->where('player_id', $user->player->id)
                ->lockForUpdate()
                ->with(['node.mapResource.resource.skill', 'node.mapResource.resource.gatheringTool'])
                ->first();

            if ($attempt === null) {
                return $this->failure('Активная добыча не найдена.', 404);
            }

            $player = Player::query()->whereKey($user->player->id)->lockForUpdate()->firstOrFail();
            $lockedUser = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $lockedUser->setRelation('player', $player);
            $lockedUser->load('currentLocation.map');

            if ($attempt->expires_at->isPast()) {
                $attempt->delete();

                return $this->failure('Время завершения добычи истекло.', 409);
            }

            if ($attempt->completes_at->isFuture()) {
                return $this->failure('Добыча ещё не завершена.', 409);
            }

            if ((int) $lockedUser->location_id !== (int) $attempt->location_id) {
                $attempt->delete();

                return $this->failure('Добыча отменена из-за перехода на другую локацию.', 409);
            }

            $blocked = $this->areaBlockReason($lockedUser, $lockedUser->currentLocation);
            if ($blocked !== null) {
                $attempt->delete();

                return $this->failure($blocked, 422);
            }

            $node = GatheringNode::query()
                ->whereKey($attempt->gathering_node_id)
                ->lockForUpdate()
                ->with(['mapResource.resource.skill', 'mapResource.resource.gatheringTool'])
                ->firstOrFail();
            if ($node->respawn_at !== null && $node->respawn_at->isFuture()) {
                $attempt->delete();

                return new GatheringActionResultDTO(
                    ok: false,
                    message: 'Добыча не удалась: ресурс уже собран другим игроком.',
                );
            }

            $resource = $node->mapResource->resource;
            $resourceBlock = $this->resourceBlockReason($player, $resource);
            if ($resourceBlock !== null) {
                $attempt->delete();

                return $this->failure($resourceBlock, 422);
            }

            $this->backpackService->addItemByShareItem($lockedUser, $resource, 1);
            $profession = $this->awardExperience($player, $resource->skill, max(1, (int) $resource->skill_exp));

            [$x, $y] = $this->randomPosition($node->mapResource, (float) $node->x_percent, (float) $node->y_percent);
            $node->x_percent = $x;
            $node->y_percent = $y;
            $node->respawn_at = now()->addSeconds(max(1, (int) $resource->gathering_respawn_seconds));
            $node->save();
            $attempt->delete();

            return new GatheringActionResultDTO(
                ok: true,
                message: sprintf('Получено: %s ×1 · опыт +%d.', $resource->name, max(1, (int) $resource->skill_exp)),
                data: [
                    'reward' => [
                        'shareItemId' => (int) $resource->id,
                        'name' => (string) $resource->name,
                        'image' => $this->gatheringImage($resource),
                        'count' => 1,
                    ],
                    'profession' => $profession,
                    'respawnAt' => $node->respawn_at->toIso8601String(),
                ],
            );
        });
    }

    public function cancel(User $user): GatheringActionResultDTO
    {
        $deleted = $this->cancelForPlayer((int) $user->player->id);

        return new GatheringActionResultDTO(
            ok: true,
            message: $deleted > 0 ? 'Добыча отменена.' : 'Активной добычи нет.',
        );
    }

    public function cancelForPlayer(int $playerId): int
    {
        return GatheringAttempt::query()->where('player_id', $playerId)->delete();
    }

    private function synchronizeNodes(int $mapId): void
    {
        $configIds = MapGatheringResource::query()->where('map_id', $mapId)->pluck('id');

        foreach ($configIds as $configId) {
            DB::transaction(function () use ($configId): void {
                $config = MapGatheringResource::query()->whereKey($configId)->lockForUpdate()->first();
                if ($config === null) {
                    return;
                }

                $missing = max(0, $config->max_active - $config->nodes()->count());
                for ($index = 0; $index < $missing; $index++) {
                    [$x, $y] = $this->randomPosition($config);
                    $config->nodes()->create([
                        'x_percent' => $x,
                        'y_percent' => $y,
                    ]);
                }
            });
        }
    }

    private function nodeState(User $user, GatheringNode $node): array
    {
        $resource = $node->mapResource->resource;
        $blocked = $this->resourceBlockReason($user->player, $resource);
        $gatheredByOtherPlayer = $node->attempts->contains(
            fn (GatheringAttempt $attempt): bool => (int) $attempt->player_id !== (int) $user->player->id,
        );

        return [
            'id' => (int) $node->id,
            'name' => (string) $resource->name,
            'description' => (string) ($resource->description ?? ''),
            'image' => $this->gatheringImage($resource),
            'rarity' => $resource->rarity->value,
            'rarityLabel' => $resource->rarity->label(),
            'rarityColor' => $resource->rarity->color(),
            'x' => (float) $node->x_percent,
            'y' => (float) $node->y_percent,
            'gatherTime' => max(1, (int) $resource->gathering_time_seconds),
            'respawnTime' => max(1, (int) $resource->gathering_respawn_seconds),
            'requiredLevel' => max(1, (int) $resource->skill_lvl),
            'experience' => max(1, (int) $resource->skill_exp),
            'professionId' => (int) $resource->skill_id,
            'professionName' => (string) $resource->skill?->name,
            'toolName' => (string) $resource->gatheringTool?->name,
            'busy' => $gatheredByOtherPlayer,
            'gatheringPlayersCount' => $node->attempts->count(),
            'ownedByPlayer' => $node->attempts->contains(
                fn (GatheringAttempt $attempt): bool => (int) $attempt->player_id === (int) $user->player->id,
            ),
            'canGather' => $blocked === null,
            'blockedReason' => $blocked,
        ];
    }

    private function gatheringImage(ShareItem $resource): string
    {
        return $resource->transparent_image ?? (string) $resource->image;
    }

    private function resourceBlockReason(Player $player, ShareItem $resource): ?string
    {
        if ($resource->type !== ShareItemType::RESOURCE
            || $resource->skill === null
            || $resource->skill->type !== 'peaceful'
            || $resource->gathering_time_seconds === null
            || $resource->gathering_respawn_seconds === null
            || $resource->gathering_tool_share_item_id === null) {
            return 'Ресурс настроен не полностью.';
        }

        $playerSkill = $this->ensureProfession($player, $resource->skill);
        if ($playerSkill->lvl < max(1, (int) $resource->skill_lvl)) {
            return sprintf('Требуется %s %d уровня.', $resource->skill->name, max(1, (int) $resource->skill_lvl));
        }

        if (! $this->hasToolInHands($player, (int) $resource->gathering_tool_share_item_id)) {
            return sprintf('Возьмите в руку инструмент «%s».', $resource->gatheringTool?->name ?? 'неизвестный инструмент');
        }

        return null;
    }

    private function areaBlockReason(User $user, ?Location $location): ?string
    {
        if ($location === null || $location->map_id === null) {
            return 'Эта локация не привязана к карте.';
        }

        if ($location->dungeon_id !== null) {
            return 'В инстансах добыча ресурсов недоступна.';
        }

        if ((int) $user->player->hp_now <= 0) {
            return 'Мёртвый персонаж не может добывать ресурсы.';
        }

        if ($this->hasActiveBattle((int) $user->id, (int) $location->id)) {
            return 'Во время боя добыча ресурсов недоступна.';
        }

        return null;
    }

    private function hasToolInHands(Player $player, int $toolShareItemId): bool
    {
        $equipment = PlayerEquipment::query()
            ->where('player_id', $player->id)
            ->with(['handLeft.itemInfo', 'handRight.itemInfo'])
            ->first();

        return in_array($toolShareItemId, [
            $equipment?->handLeft?->share_item_id,
            $equipment?->handRight?->share_item_id,
        ], true);
    }

    private function hasActiveBattle(int $userId, int $locationId): bool
    {
        return BattleDetail::query()
            ->where('user_id', $userId)
            ->where('status', BattleDetailStatus::LIFE->value)
            ->whereHas('battle', fn ($query) => $query
                ->where('status', BattleStatus::ACTIVE->value)
                ->where('location_id', $locationId))
            ->exists();
    }

    private function ensureProfession(Player $player, Skill $skill): PlayerSkill
    {
        $requirement = SkillLevelRequirement::query()
            ->where('skill_id', $skill->id)
            ->where('lvl', 1)
            ->first();

        return PlayerSkill::query()->firstOrCreate(
            ['player_id' => $player->id, 'skill_id' => $skill->id],
            [
                'lvl' => 1,
                'exp' => 0,
                'exp_up' => $requirement?->exp_required ?? 100,
                'exp_diff' => $requirement?->exp_diff ?? 100,
            ],
        );
    }

    private function awardExperience(Player $player, Skill $skill, int $amount): array
    {
        $playerSkill = PlayerSkill::query()
            ->where('player_id', $player->id)
            ->where('skill_id', $skill->id)
            ->lockForUpdate()
            ->first() ?? $this->ensureProfession($player, $skill);

        $playerSkill->exp += max(1, $amount);
        while ($playerSkill->exp >= $playerSkill->exp_up) {
            $nextLevel = $playerSkill->lvl + 1;
            $requirement = SkillLevelRequirement::query()
                ->where('skill_id', $skill->id)
                ->where('lvl', $nextLevel)
                ->first();

            if ($requirement === null) {
                break;
            }

            $playerSkill->lvl = $nextLevel;
            $playerSkill->exp_up = $requirement->exp_required;
            $playerSkill->exp_diff = $requirement->exp_diff;
        }
        $playerSkill->save();

        return $this->professionState($playerSkill);
    }

    private function professionStates(Player $player): array
    {
        return Skill::query()
            ->where('type', 'peaceful')
            ->whereIn('name', self::PROFESSION_NAMES)
            ->orderByRaw("CASE name WHEN 'Травник' THEN 1 WHEN 'Рыбак' THEN 2 ELSE 3 END")
            ->get()
            ->map(fn (Skill $skill): array => $this->professionState($this->ensureProfession($player, $skill)))
            ->all();
    }

    private function professionState(PlayerSkill $playerSkill): array
    {
        $playerSkill->loadMissing('skill');
        $levelStart = max(0, $playerSkill->exp_up - $playerSkill->exp_diff);

        return [
            'id' => (int) $playerSkill->skill_id,
            'name' => (string) $playerSkill->skill->name,
            'level' => (int) $playerSkill->lvl,
            'experience' => (int) $playerSkill->exp,
            'levelExperience' => max(0, (int) $playerSkill->exp - $levelStart),
            'levelExperienceRequired' => max(1, (int) $playerSkill->exp_diff),
        ];
    }

    private function randomPosition(MapGatheringResource $config, ?float $previousX = null, ?float $previousY = null): array
    {
        for ($attempt = 0; $attempt < 24; $attempt++) {
            $x = random_int($config->min_x * 100, $config->max_x * 100) / 100;
            $y = random_int($config->min_y * 100, $config->max_y * 100) / 100;

            if ($previousX === null || $previousY === null || hypot($x - $previousX, $y - $previousY) >= 18) {
                return [$x, $y];
            }
        }

        return [$x, $y];
    }

    private function deleteExpiredAttempts(): void
    {
        GatheringAttempt::query()->where('expires_at', '<=', now())->delete();
    }

    private function cancelIfContextChanged(User $user): void
    {
        GatheringAttempt::query()
            ->where('player_id', $user->player->id)
            ->where('location_id', '!=', $user->location_id)
            ->delete();
    }

    private function failure(string $message, int $httpCode): GatheringActionResultDTO
    {
        return new GatheringActionResultDTO(ok: false, message: $message, httpCode: $httpCode);
    }
}
