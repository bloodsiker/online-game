<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\Services;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Battle\Application\DTOs\AttackResultDTO;
use App\Modules\Battle\Application\Services\Combat\BattleEffectService;
use App\Modules\Battle\Domain\Enums\BattleDetailStatus;
use App\Modules\Battle\Domain\Enums\BattleStatus;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleDetail;
use App\Modules\Effect\Infrastructure\Persistence\Models\Effect;
use App\Modules\Item\Application\DTOs\LockpickingActionResultDTO;
use App\Modules\Item\Domain\Services\ItemService;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Item\Infrastructure\Persistence\Models\LockpickingAttempt;
use App\Modules\Item\Infrastructure\Persistence\Models\LockpickingLog;
use App\Modules\Location\Infrastructure\Persistence\Models\GatheringAttempt;
use App\Modules\Player\Domain\Services\PeacefulProfessionExperienceService;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerSkill;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Skill\Infrastructure\Persistence\Models\Skill;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

class LockpickingService
{
    public const SKILL_NAME = 'Взломщик';

    private const CLAIM_GRACE_SECONDS = 30;

    public function __construct(
        private readonly ItemService $itemService,
        private readonly PeacefulProfessionExperienceService $experienceService,
        private readonly BattleEffectService $battleEffectService,
        private readonly PlayerStatService $statService,
    ) {}

    public function page(User $user, int $itemId): array
    {
        $context = $this->itemService->accessibleChest($user, $itemId);
        abort_if($context === null, 404);

        [$item, $source] = $context;
        $config = $item->itemInfo->lockConfig;
        abort_if($config === null || $config->lock_required_skill <= 0, 404);

        $this->removeExpiredAttempt($user->player_id);
        $attempt = LockpickingAttempt::query()
            ->where('player_id', $user->player_id)
            ->where('item_id', $item->id)
            ->first();
        $skillLevel = $this->skillLevel($user->player);
        $successChance = $this->successChance(
            $skillLevel,
            $config->lock_required_skill,
            $config->trap_chance_penalty_percent,
            $config->minimum_success_chance_percent,
        );
        $lockpicks = $this->availableLockpicks($user, $config, $skillLevel);
        $selected = $attempt?->lockpick_share_item_id
            ? collect($lockpicks)->firstWhere('shareItemId', (int) $attempt->lockpick_share_item_id)
            : null;
        $selected ??= collect($lockpicks)->firstWhere('recommended', true);

        return [
            'item' => $item,
            'source' => $source,
            'skillLevel' => $skillLevel,
            'requiredSkill' => $config->lock_required_skill,
            'successChance' => $selected['successChance'] ?? $successChance,
            'difficulty' => $selected['difficulty'] ?? $this->difficultyForChance($successChance),
            'durationSeconds' => $selected['durationSeconds'] ?? $this->durationSeconds($config->lock_duration_seconds, $skillLevel, $config->lock_required_skill),
            'lockpicks' => $lockpicks,
            'selectedLockpickId' => $selected['shareItemId'] ?? null,
            'attempt' => $attempt,
            'competitors' => $this->competitorCount($item->id, $user->player_id),
            'hasLockpick' => collect($lockpicks)->contains('canUse', true),
        ];
    }

    public function requiresLockpicking(User $user, int $itemId): bool
    {
        $context = $this->itemService->accessibleChest($user, $itemId);

        return $context !== null && ($context[0]->itemInfo->lockConfig?->lock_required_skill ?? 0) > 0;
    }

    public function isLockedChest(int $itemId): bool
    {
        return Item::query()
            ->whereKey($itemId)
            ->where('is_open', false)
            ->whereHas('itemInfo', fn ($query) => $query
                ->where('type', ShareItemType::CHEST->value)
                ->whereHas('lockConfig', fn ($query) => $query->where('lock_required_skill', '>', 0)))
            ->exists();
    }

    public function start(User $user, int $itemId, ?int $lockpickShareItemId = null): LockpickingActionResultDTO
    {
        return DB::transaction(function () use ($user, $itemId, $lockpickShareItemId): LockpickingActionResultDTO {
            $player = Player::query()->whereKey($user->player_id)->lockForUpdate()->firstOrFail();
            $lockedUser = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $lockedUser->setRelation('player', $player);
            $lockedUser->load('currentLocation');

            if ($blocked = $this->actionBlockReason($lockedUser)) {
                return new LockpickingActionResultDTO(false, $blocked, 422);
            }

            LockpickingAttempt::query()->where('expires_at', '<=', now())->delete();

            $item = Item::query()->with('itemInfo.lockConfig')->whereKey($itemId)->lockForUpdate()->first();
            $context = $item === null ? null : $this->itemService->accessibleChest($lockedUser, $itemId, $item);
            $config = $item?->itemInfo->lockConfig;
            if ($context === null || $config === null || $config->lock_required_skill <= 0) {
                return new LockpickingActionResultDTO(false, 'Сундук больше недоступен.', 409);
            }
            if ($item->is_open) {
                return new LockpickingActionResultDTO(false, 'Сундук уже открыт.', 409);
            }
            $lockpick = $this->selectedLockpick($lockedUser, $lockpickShareItemId, true);
            if ($lockpick === null) {
                return new LockpickingActionResultDTO(false, 'Для взлома нужна отмычка.', 422);
            }
            $skillLevel = $this->skillLevel($player);
            $lockpickTier = max(1, (int) ($lockpick->item->itemInfo->lockpickConfig?->tier ?? 1));
            $requiredLockpickSkill = $this->lockpickRequiredSkill($lockpickTier);
            if ($skillLevel < $requiredLockpickSkill) {
                return new LockpickingActionResultDTO(false, sprintf(
                    'Для этой отмычки требуется навык «%s» %d уровня.',
                    self::SKILL_NAME,
                    $requiredLockpickSkill,
                ), 422);
            }

            $existing = LockpickingAttempt::query()->where('player_id', $user->player_id)->lockForUpdate()->first();
            if ($existing !== null) {
                return new LockpickingActionResultDTO(false, 'Вы уже взламываете другой сундук.', 409);
            }

            [$lockedItem, $source] = $context;
            $lockpickInfo = $lockpick->item->itemInfo;
            $lockpickConfig = $lockpickInfo->lockpickConfig;
            $speedBonus = (int) ($lockpickConfig?->speed_bonus_percent ?? 0);
            $duration = $this->lockpickDurationSeconds($config->lock_duration_seconds, $skillLevel, $config->lock_required_skill, $lockpickTier, $speedBonus);
            $chance = $this->lockpickSuccessChance(
                $skillLevel,
                $config->lock_required_skill,
                $config->trap_chance_penalty_percent,
                $lockpickTier,
                $config->minimum_success_chance_percent,
            );
            $startedAt = now();
            $attempt = LockpickingAttempt::query()->create([
                'player_id' => $user->player_id,
                'item_id' => $lockedItem->id,
                'share_item_id' => $lockedItem->share_item_id,
                'lockpick_share_item_id' => $lockpickInfo->id,
                'location_id' => $source === 'world' ? $lockedUser->location_id : null,
                'is_inventory' => $source === 'inventory',
                'skill_snapshot' => $skillLevel,
                'lockpick_tier_snapshot' => $lockpickTier,
                'speed_bonus_snapshot' => $speedBonus,
                'failure_preserve_chance_snapshot' => (int) ($lockpickConfig?->failure_preserve_chance_percent ?? 0),
                'trap_avoid_chance_snapshot' => (int) ($lockpickConfig?->trap_avoid_chance_percent ?? 0),
                'chance_snapshot' => $chance,
                'started_at' => $startedAt,
                'completes_at' => $startedAt->copy()->addSeconds($duration),
                'expires_at' => $startedAt->copy()->addSeconds($duration + self::CLAIM_GRACE_SECONDS),
            ]);

            return new LockpickingActionResultDTO(true, 'Взлом начат.', 200, [
                'status' => 'started',
                'completes_at' => $attempt->completes_at->toIso8601String(),
                'duration_seconds' => $duration,
                'success_chance' => $chance,
                'lockpick_share_item_id' => $lockpickInfo->id,
                'competitors' => $this->competitorCount($lockedItem->id, $user->player_id),
            ]);
        });
    }

    public function complete(User $user, int $itemId): LockpickingActionResultDTO
    {
        return DB::transaction(function () use ($user, $itemId): LockpickingActionResultDTO {
            $player = Player::query()->whereKey($user->player_id)->lockForUpdate()->firstOrFail();
            $lockedUser = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $lockedUser->setRelation('player', $player);
            $lockedUser->load('currentLocation');
            $attempt = LockpickingAttempt::query()
                ->where('player_id', $player->id)
                ->where('item_id', $itemId)
                ->lockForUpdate()
                ->first();

            if ($attempt === null) {
                return new LockpickingActionResultDTO(false, 'Активная попытка не найдена.', 409);
            }
            if (now()->lt($attempt->completes_at)) {
                return new LockpickingActionResultDTO(false, 'Замок ещё не вскрыт.', 425, [
                    'completes_at' => $attempt->completes_at->toIso8601String(),
                ]);
            }
            if (now()->gt($attempt->expires_at)) {
                $attempt->delete();

                return new LockpickingActionResultDTO(false, 'Время на завершение попытки истекло.', 409);
            }
            if ($blocked = $this->actionBlockReason($lockedUser)) {
                $attempt->delete();

                return new LockpickingActionResultDTO(false, $blocked, 422, ['status' => 'cancelled']);
            }
            if (! $attempt->is_inventory && (int) $attempt->location_id !== (int) $lockedUser->location_id) {
                $attempt->delete();

                return new LockpickingActionResultDTO(false, 'Взлом прерван: вы покинули локацию.', 409);
            }

            $item = Item::query()->with('itemInfo.lockConfig.trapEffect')->whereKey($itemId)->lockForUpdate()->first();
            $context = $item === null ? null : $this->itemService->accessibleChest($lockedUser, $itemId, $item);
            if ($context === null) {
                $this->writeLog($attempt, $item, 'taken', false);
                $attempt->delete();

                return new LockpickingActionResultDTO(true, 'Сундук уже открыл другой игрок.', 200, ['status' => 'taken']);
            }
            $lockpick = $this->selectedLockpick($lockedUser, $attempt->lockpick_share_item_id, true);
            if ($lockpick === null) {
                return new LockpickingActionResultDTO(false, 'Отмычки больше нет в рюкзаке.', 422);
            }

            $success = random_int(1, 10_000) <= (int) round($attempt->chance_snapshot * 100);
            if (! $success) {
                $lockpickBroken = random_int(1, 100) > (int) $attempt->failure_preserve_chance_snapshot;
                if ($lockpickBroken) {
                    $this->consumeLockpick($lockpick);
                }
                $config = $item->itemInfo->lockConfig;
                $hasTrap = ($config?->trap_chance_penalty_percent ?? 0) > 0
                    || $config?->trap_effect_id !== null
                    || ($config?->trap_damage_percent ?? 0) > 0;
                $trapAvoided = $hasTrap && random_int(1, 100) <= (int) $attempt->trap_avoid_chance_snapshot;
                $trapTriggered = $hasTrap && ! $trapAvoided;
                $trapResult = ['damage' => 0, 'effect' => null];
                if ($trapTriggered) {
                    $trapResult = $this->triggerTrap(
                        $player,
                        $config->trapEffect,
                        $config->trap_effect_duration_seconds,
                        $config->trap_damage_percent,
                    );
                }
                $this->writeLog($attempt, $item, 'failure', $trapTriggered, $lockpickBroken, $trapAvoided);
                $attempt->delete();
                $hasLockpick = collect($this->availableLockpicks($lockedUser, $config, (int) $attempt->skill_snapshot))
                    ->contains('canUse', true);

                $message = $lockpickBroken ? 'Замок не поддался, отмычка сломана.' : 'Замок не поддался, но отмычка сохранилась.';
                if ($trapAvoided) {
                    $message .= ' Ловушка обезврежена.';
                }
                if ($trapTriggered) {
                    $message .= ' Ловушка сработала.';
                    if ($trapResult['damage'] > 0) {
                        $message .= sprintf(' Получено урона: %d HP.', $trapResult['damage']);
                    }
                }
                if (! $hasLockpick) {
                    $message .= ' Отмычек больше нет.';
                }

                return new LockpickingActionResultDTO(true, $message, 200, [
                    'status' => 'failure',
                    'trap_damage' => $trapResult['damage'],
                    'trap_effect' => $trapResult['effect'],
                    'has_lockpick' => $hasLockpick,
                    'lockpick_broken' => $lockpickBroken,
                    'trap_avoided' => $trapAvoided,
                ]);
            }

            $eventCollection = $context[1] === 'world'
                ? $this->itemService->recordWorldEventChestOpened($lockedUser, $item)
                : null;
            if ($eventCollection !== null && ! $eventCollection->allowed) {
                $attempt->delete();

                return new LockpickingActionResultDTO(
                    false,
                    $eventCollection->error ?? 'Цель события больше недоступна.',
                    409,
                );
            }

            $this->writeLog($attempt, $item, 'success', false);
            $loot = $this->itemService->claimAllChestContents($lockedUser, $item);
            $skill = $this->lockpickingSkill();
            if ($skill !== null) {
                $experience = max(1, (int) ($item->itemInfo->lockConfig->experience_reward
                    ?: ceil($item->itemInfo->lockConfig->lock_required_skill / 10)));
                $this->experienceService->award($player, $skill, $experience);
            }
            $attempt->delete();

            return new LockpickingActionResultDTO(true, $loot === []
                ? 'Сундук открыт, но он оказался пуст.'
                : 'Сундук успешно открыт.', 200, [
                    'status' => 'success',
                    'loot' => $loot,
                    'money' => (int) $lockedUser->money,
                    'event_progress' => $eventCollection === null ? null : [
                        'player' => $eventCollection->playerProgress,
                        'limit' => $eventCollection->playerLimit,
                        'influence_awarded' => $eventCollection->influenceAwarded,
                        'map_influence' => $eventCollection->mapInfluence,
                    ],
                ]);
        });
    }

    public function cancel(User $user, int $itemId): LockpickingActionResultDTO
    {
        return DB::transaction(function () use ($user, $itemId): LockpickingActionResultDTO {
            $attempt = LockpickingAttempt::query()
                ->where('player_id', $user->player_id)
                ->where('item_id', $itemId)
                ->lockForUpdate()
                ->first();
            if ($attempt === null) {
                return new LockpickingActionResultDTO(false, 'Активная попытка не найдена.', 404);
            }

            $item = Item::query()->with('itemInfo')->find($itemId);
            $this->writeLog($attempt, $item, 'cancelled', false);
            $attempt->delete();

            return new LockpickingActionResultDTO(true, 'Взлом отменён.', 200, ['status' => 'cancelled']);
        });
    }

    public function successChance(
        int $skillLevel,
        int $requiredSkill,
        int $trapChancePenaltyPercent,
        int $minimumSuccessChancePercent,
    ): float {
        $difference = $skillLevel - max(1, $requiredSkill);
        $chance = $difference >= 0
            ? 80 + $difference * 0.15
            : 80 + $difference * 1.5;
        $chance -= min(95, max(0, $trapChancePenaltyPercent));
        $minimumChance = min(95, max(0, $minimumSuccessChancePercent));

        return round(min(95, max($minimumChance, $chance)), 2);
    }

    public function durationSeconds(int $baseSeconds, int $skillLevel, int $requiredSkill): int
    {
        $bonus = min(40, max(0, $skillLevel - max(1, $requiredSkill)) * 0.2);

        return max(2, (int) ceil(max(2, $baseSeconds) * (1 - $bonus / 100)));
    }

    public function lockpickSuccessChance(
        int $skillLevel,
        int $requiredSkill,
        int $trapPenalty,
        int $lockpickTier,
        int $minimumSuccessChancePercent,
    ): float {
        $tierGap = max(0, $this->lockTier($requiredSkill) - max(1, min(6, $lockpickTier)));
        $minimumChance = min(95, max(0, $minimumSuccessChancePercent));

        return round(max(
            $minimumChance,
            $this->successChance($skillLevel, $requiredSkill, $trapPenalty, $minimumChance) - $tierGap * 10,
        ), 2);
    }

    public function lockpickDurationSeconds(int $baseSeconds, int $skillLevel, int $requiredSkill, int $lockpickTier, int $speedBonus): int
    {
        $tierGap = max(0, $this->lockTier($requiredSkill) - max(1, min(6, $lockpickTier)));
        $seconds = $this->durationSeconds($baseSeconds, $skillLevel, $requiredSkill) * (1 + $tierGap * 0.15);

        return max(2, (int) ceil($seconds * (1 - min(90, max(0, $speedBonus)) / 100)));
    }

    /** @return array{label: string, level: string} */
    public function difficultyForChance(float $chance): array
    {
        return match (true) {
            $chance >= 80 => ['label' => 'Легко', 'level' => 'easy'],
            $chance >= 55 => ['label' => 'Среднее', 'level' => 'medium'],
            $chance >= 30 => ['label' => 'Сложно', 'level' => 'hard'],
            default => ['label' => 'Очень сложно', 'level' => 'very-hard'],
        };
    }

    private function skillLevel(Player $player): int
    {
        $skill = $this->lockpickingSkill();
        if ($skill === null) {
            return 1;
        }

        return max(1, (int) (PlayerSkill::query()
            ->where('player_id', $player->id)
            ->where('skill_id', $skill->id)
            ->value('lvl') ?? 1));
    }

    private function lockpickingSkill(): ?Skill
    {
        return Skill::query()->where('name', self::SKILL_NAME)->first();
    }

    private function lockpickBackpackQuery(User $user, bool $lock = false)
    {
        $query = Backpack::query()
            ->where('user_id', $user->id)
            ->where('equipped', 0)
            ->where('count', '>', 0)
            ->whereHas('item.itemInfo', fn ($query) => $query->where('is_lockpick', true));

        return $lock ? $query->lockForUpdate() : $query;
    }

    private function selectedLockpick(User $user, ?int $shareItemId, bool $lock = false): ?Backpack
    {
        return $this->lockpickBackpackQuery($user, $lock)
            ->when($shareItemId !== null, fn ($query) => $query->whereHas('item', fn ($q) => $q->where('share_item_id', $shareItemId)))
            ->with('item.itemInfo.lockpickConfig')
            ->orderBy('id')
            ->first();
    }

    private function availableLockpicks(User $user, object $chestConfig, int $skillLevel): array
    {
        return $this->lockpickBackpackQuery($user)
            ->with('item.itemInfo.lockpickConfig')
            ->get()
            ->groupBy(fn (Backpack $row) => (int) $row->item->share_item_id)
            ->map(function ($rows) use ($chestConfig, $skillLevel): array {
                $info = $rows->first()->item->itemInfo;
                $config = $info->lockpickConfig;
                $tier = max(1, (int) ($config?->tier ?? 1));
                $requiredSkill = $this->lockpickRequiredSkill($tier);
                $chance = $this->lockpickSuccessChance(
                    $skillLevel,
                    $chestConfig->lock_required_skill,
                    $chestConfig->trap_chance_penalty_percent,
                    $tier,
                    $chestConfig->minimum_success_chance_percent,
                );
                $duration = $this->lockpickDurationSeconds($chestConfig->lock_duration_seconds, $skillLevel, $chestConfig->lock_required_skill, $tier, (int) ($config?->speed_bonus_percent ?? 0));

                return [
                    'shareItemId' => (int) $info->id,
                    'name' => (string) $info->name,
                    'image' => (string) $info->image,
                    'count' => (int) $rows->sum('count'),
                    'tier' => $tier,
                    'requiredSkill' => $requiredSkill,
                    'canUse' => $skillLevel >= $requiredSkill,
                    'speedBonus' => (int) ($config?->speed_bonus_percent ?? 0),
                    'preserveChance' => (int) ($config?->failure_preserve_chance_percent ?? 0),
                    'trapAvoidChance' => (int) ($config?->trap_avoid_chance_percent ?? 0),
                    'successChance' => $chance,
                    'difficulty' => $this->difficultyForChance($chance),
                    'durationSeconds' => $duration,
                    'recommended' => false,
                ];
            })
            ->sortBy(fn (array $pick) => [! $pick['canUse'], $pick['tier'] < $this->lockTier($chestConfig->lock_required_skill), $pick['tier']])
            ->values()
            ->map(function (array $pick, int $index): array {
                $pick['recommended'] = $index === 0 && $pick['canUse'];

                return $pick;
            })
            ->all();
    }

    public function lockTier(int $requiredSkill): int
    {
        return min(6, max(1, intdiv(max(1, $requiredSkill), 50) + 1));
    }

    private function lockpickRequiredSkill(int $tier): int
    {
        return 1 + (max(1, min(6, $tier)) - 1) * 50 - (max(1, min(6, $tier)) === 1 ? 0 : 1);
    }

    private function consumeLockpick(Backpack $backpack): void
    {
        if ($backpack->count > 1) {
            $backpack->decrement('count');

            return;
        }

        $item = $backpack->item;
        $backpack->delete();
        $item?->delete();
    }

    /** @return array{damage: int, effect: array{id: int, name: string, image: string|null, description: string|null, duration_seconds: int}|null} */
    private function triggerTrap(Player $player, ?Effect $effect, int $duration, int $damagePercent): array
    {
        $damage = 0;
        if ($damagePercent > 0) {
            $maxHp = max(1, (int) $this->statService->resolve($player)->hpMax);
            $damage = max(1, (int) ceil($maxHp * min(100, $damagePercent) / 100));
            $hpBefore = (int) $player->hp_now;
            $player->hp_now = max(1, $hpBefore - $damage);
            $damage = $hpBefore - (int) $player->hp_now;
            $player->save();
        }
        if ($effect !== null) {
            $this->battleEffectService->applyEffectToPlayer(
                $effect,
                $player,
                null,
                new AttackResultDTO,
                max(1, $duration),
            );
        }

        return [
            'damage' => $damage,
            'effect' => $effect === null ? null : [
                'id' => (int) $effect->id,
                'name' => $effect->name,
                'image' => $effect->image,
                'description' => $effect->description,
                'duration_seconds' => max(1, $duration),
            ],
        ];
    }

    private function competitorCount(int $itemId, int $playerId): int
    {
        return LockpickingAttempt::query()
            ->where('item_id', $itemId)
            ->where('player_id', '!=', $playerId)
            ->where('expires_at', '>', now())
            ->count();
    }

    private function removeExpiredAttempt(int $playerId): void
    {
        LockpickingAttempt::query()->where('player_id', $playerId)->where('expires_at', '<=', now())->delete();
    }

    private function actionBlockReason(User $user, bool $checkGathering = true): ?string
    {
        if ((int) $user->player->hp_now <= 0) {
            return 'Мёртвый персонаж не может взламывать сундуки.';
        }

        if (BattleDetail::query()
            ->where('user_id', $user->id)
            ->where('status', BattleDetailStatus::LIFE->value)
            ->whereHas('battle', fn ($query) => $query
                ->where('status', BattleStatus::ACTIVE->value)
                ->where('location_id', $user->location_id))
            ->exists()) {
            return 'Во время боя взлом сундуков недоступен.';
        }

        if ($checkGathering && GatheringAttempt::query()
            ->where('player_id', $user->player_id)
            ->where('expires_at', '>', now())
            ->exists()) {
            return 'Сначала завершите добычу ресурса.';
        }

        return null;
    }

    private function writeLog(LockpickingAttempt $attempt, ?Item $item, string $result, bool $trapTriggered, bool $lockpickBroken = false, bool $trapAvoided = false): void
    {
        LockpickingLog::query()->create([
            'player_id' => $attempt->player_id,
            'item_id' => $attempt->item_id,
            'share_item_id' => $item?->share_item_id ?? $attempt->share_item_id,
            'lockpick_share_item_id' => $attempt->lockpick_share_item_id,
            'source' => $attempt->is_inventory ? 'inventory' : 'world',
            'result' => $result,
            'skill_level' => $attempt->skill_snapshot,
            'lockpick_tier' => $attempt->lockpick_tier_snapshot,
            'success_chance' => $attempt->chance_snapshot,
            'trap_triggered' => $trapTriggered,
            'lockpick_broken' => $lockpickBroken,
            'trap_avoided' => $trapAvoided,
        ]);
    }
}
