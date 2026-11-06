<?php

declare(strict_types=1);

namespace App\Modules\Clan\Domain\Services;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Clan\Domain\Enums\ClanLogAction;
use App\Modules\Clan\Domain\Enums\ClanPermission;
use App\Modules\Clan\Domain\Models\Clan;
use App\Modules\Clan\Domain\Models\ClanLearnedSkill;
use App\Modules\Clan\Domain\Models\ClanSkillDefinition;
use App\Modules\Clan\Domain\Models\ClanSkillLevel;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerMagicSkill;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ClanSkillService
{
    public function __construct(
        private readonly ClanLogService $clanLogService,
        private readonly ClanActionTransaction $actions,
    ) {}

    /**
     * Attempt to learn (or upgrade) a clan skill.
     * Returns null on success, or an error message string.
     */
    public function learn(Clan $clan, ClanSkillDefinition $definition, Player $player, User $user, int $expectedLevel = 0): ?string
    {
        try {
            $this->actions->run(clanId: (int) $clan->id, userIds: collect([$user->id]), action: function (Clan $clan, Collection $users) use ($definition, $user, $expectedLevel): void {
                $user = $users->get($user->id);
                $membership = $user->clanMembership;
                if ($membership === null || (int) $membership->clan_id !== (int) $clan->id
                    || ! $membership->role?->hasPermission(ClanPermission::LEARN_SKILL)) {
                    throw new \RuntimeException('У вас нет прав изучать навыки клана.');
                }
                if (! $clan->hasPaidTax()) {
                    throw new \RuntimeException('Клановые навыки заблокированы до оплаты ежемесячного налога.');
                }
                $players = Player::whereIn('id', $users->pluck('player_id')->filter())->orderBy('id')->lockForUpdate()->get()->keyBy('id');
                $player = $players->get($user->player_id);
                if ($player === null || (int) $player->user_id !== (int) $user->id) {
                    throw new \RuntimeException('Персонаж не найден.');
                }
                $definition = ClanSkillDefinition::findOrFail($definition->id);
                $learned = $clan->learnedSkills()->where('clan_skill_definition_id', $definition->id)->lockForUpdate()->first();
                $currentLevel = (int) ($learned?->current_level ?? 0);
                if ($currentLevel !== $expectedLevel) {
                    throw new \RuntimeException(__('clan.action.skill_changed'));
                }
                $nextLevel = $currentLevel + 1;
                if ($nextLevel > $definition->max_level) {
                    throw new \RuntimeException('Навык уже достиг максимального уровня.');
                }
                $levelData = $definition->levels()->where('level', $nextLevel)->first();
                if ($levelData === null) {
                    throw new \RuntimeException('Данные уровня навыка не найдены.');
                }
                if ($clan->lvl < $levelData->required_clan_level) {
                    throw new \RuntimeException("Недостаточный уровень клана. Требуется: {$levelData->required_clan_level}.");
                }
                if ($clan->points < $levelData->required_bonus_points || $levelData->required_bonus_points < 0) {
                    throw new \RuntimeException("Недостаточно бонусных очков. Требуется: {$levelData->required_bonus_points}.");
                }
                if ($user->money < $levelData->required_money || $levelData->required_money < 0) {
                    throw new \RuntimeException("Недостаточно монет. Требуется: {$levelData->required_money}.");
                }
                if ($levelData->required_bonus_points > 0
                    && Clan::whereKey($clan->id)->where('points', '>=', $levelData->required_bonus_points)->decrement('points', $levelData->required_bonus_points) !== 1) {
                    throw new \RuntimeException('Недостаточно бонусных очков.');
                }
                if ($levelData->required_money > 0
                    && User::whereKey($user->id)->where('money', '>=', $levelData->required_money)->decrement('money', $levelData->required_money) !== 1) {
                    throw new \RuntimeException('Недостаточно монет.');
                }
                $this->consumeItemRequirements($player, $this->itemRequirements($levelData));
                if ($learned !== null) {
                    $learned->update(['current_level' => $nextLevel]);
                } else {
                    try {
                        DB::transaction(fn () => ClanLearnedSkill::create([
                            'clan_id' => $clan->id, 'clan_skill_definition_id' => $definition->id, 'current_level' => $nextLevel,
                        ]));
                    } catch (UniqueConstraintViolationException $exception) {
                        // The outer transaction must roll back costs before returning a friendly error.
                        throw new \RuntimeException(__('clan.action.skill_changed'), previous: $exception);
                    }
                }
                $this->syncSkillForAllMembers($clan, $definition, $currentLevel, $nextLevel, $levelData);
                $this->clanLogService->write($clan, $user->id,
                    $currentLevel === 0 ? ClanLogAction::SKILL_LEARNED : ClanLogAction::SKILL_UPGRADED,
                    'Изучен уровень '.$nextLevel.' навыка «'.$definition->getTranslation('name', 'ru', false).'»');
            }, allMembers: true);
        } catch (QueryException $exception) {
            throw $exception;
        } catch (\RuntimeException $exception) {
            return $exception->getMessage();
        }

        return null;
    }

    /** @return Collection<int, array{share_item_id: int, count: int, name: string}> */
    private function itemRequirements(ClanSkillLevel $levelData): Collection
    {
        $requirements = $levelData->itemRequirements
            ->map(fn ($requirement) => [
                'share_item_id' => (int) $requirement->share_item_id,
                'count' => (int) $requirement->count,
                'name' => $requirement->shareItem?->name ?? 'Предмет',
            ]);

        if ($requirements->isEmpty() && $levelData->share_item_id !== null) {
            $requirements = collect([[
                'share_item_id' => (int) $levelData->share_item_id,
                'count' => (int) ($levelData->share_item_count ?? 1),
                'name' => $levelData->stoneItem?->name ?? 'Предмет',
            ]]);
        }
        if ($requirements->contains(fn ($requirement) => $requirement['count'] < 1)) {
            throw new \RuntimeException(__('clan.action.invalid_materials'));
        }

        return $requirements->groupBy('share_item_id')->map(fn ($group) => [
            ...$group->first(), 'count' => (int) $group->sum('count'),
        ])->values();
    }

    private function consumeItemRequirements(Player $player, Collection $requirements): void
    {
        $available = $this->backpacksByShareItem($player, $requirements, lock: true);

        foreach ($requirements as $requirement) {
            $backpacks = $available->get($requirement['share_item_id'], collect());
            if ($backpacks->sum('count') < $requirement['count']) {
                throw new \RuntimeException("В рюкзаке недостаточно предметов «{$requirement['name']}». Нужно: {$requirement['count']}.");
            }

            $remaining = $requirement['count'];
            foreach ($backpacks as $backpack) {
                $taken = min($remaining, $backpack->count);
                if ($backpack->count <= $taken) {
                    $backpack->delete();
                } else {
                    $backpack->decrement('count', $taken);
                }

                $remaining -= $taken;
                if ($remaining === 0) {
                    break;
                }
            }
        }
    }

    private function backpacksByShareItem(Player $player, Collection $requirements, bool $lock = false): Collection
    {
        if ($requirements->isEmpty()) {
            return collect();
        }

        $query = Backpack::query()
            ->where('user_id', $player->user_id)
            ->whereHas('item', fn ($q) => $q->whereIn('share_item_id', $requirements->pluck('share_item_id')))
            ->with('item')->orderBy('id');

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->get()->groupBy(fn (Backpack $backpack) => $backpack->item->share_item_id);
    }

    private function syncSkillForAllMembers(
        Clan $clan,
        ClanSkillDefinition $definition,
        int $oldLevel,
        int $newLevel,
        ClanSkillLevel $newLevelData,
    ): void {
        $playerIds = $clan->members()
            ->with('user.player')
            ->get()
            ->pluck('user.player.id')
            ->filter()
            ->values();

        if ($playerIds->isEmpty()) {
            return;
        }

        if ($oldLevel > 0) {
            $oldLevelData = $definition->levels()->where('level', $oldLevel)->first();
            if ($oldLevelData?->magic_skill_id) {
                PlayerMagicSkill::whereIn('player_id', $playerIds)
                    ->where('magic_skill_id', $oldLevelData->magic_skill_id)
                    ->delete();
            }
        }

        if ($newLevelData->magic_skill_id) {
            $rows = $playerIds->map(fn ($playerId) => [
                'player_id' => $playerId,
                'magic_skill_id' => $newLevelData->magic_skill_id,
                'is_equipped' => true,
            ])->toArray();

            PlayerMagicSkill::upsert($rows, ['player_id', 'magic_skill_id'], ['is_equipped']);
        }
    }

    public function applyAllSkillsToPlayer(Player $player, Clan $clan): void
    {
        $learnedSkills = $clan->learnedSkills()->with('definition.levels')->get();

        foreach ($learnedSkills as $learned) {
            $levelData = $learned->definition->levels
                ->firstWhere('level', $learned->current_level);

            if ($levelData?->magic_skill_id) {
                PlayerMagicSkill::updateOrCreate(
                    ['player_id' => $player->id, 'magic_skill_id' => $levelData->magic_skill_id],
                    ['is_equipped' => true],
                );
            }
        }
    }

    public function removeAllSkillsFromPlayer(Player $player, Clan $clan): void
    {
        $magicSkillIds = $clan->learnedSkills()
            ->with('definition.levels')
            ->get()
            ->flatMap(function ($learned) {
                $levelData = $learned->definition->levels
                    ->firstWhere('level', $learned->current_level);

                return $levelData?->magic_skill_id ? [$levelData->magic_skill_id] : [];
            });

        if ($magicSkillIds->isNotEmpty()) {
            PlayerMagicSkill::where('player_id', $player->id)
                ->whereIn('magic_skill_id', $magicSkillIds)
                ->delete();
        }
    }

    public function applyAllSkillsToClanMembers(Clan $clan): void
    {
        $playerIds = $clan->members()
            ->with('user.player:id,user_id')
            ->get()
            ->pluck('user.player.id')
            ->filter()
            ->values();
        $magicSkillIds = $this->currentMagicSkillIds($clan);

        if ($playerIds->isEmpty() || $magicSkillIds->isEmpty()) {
            return;
        }

        $rows = $playerIds->flatMap(fn ($playerId) => $magicSkillIds->map(fn ($magicSkillId) => [
            'player_id' => $playerId,
            'magic_skill_id' => $magicSkillId,
            'is_equipped' => true,
        ]))->all();

        PlayerMagicSkill::upsert($rows, ['player_id', 'magic_skill_id'], ['is_equipped']);
    }

    public function removeAllSkillsFromClanMembers(Clan $clan): void
    {
        $playerIds = $clan->members()
            ->with('user.player:id,user_id')
            ->get()
            ->pluck('user.player.id')
            ->filter()
            ->values();
        $magicSkillIds = $this->currentMagicSkillIds($clan);

        if ($playerIds->isEmpty() || $magicSkillIds->isEmpty()) {
            return;
        }

        PlayerMagicSkill::query()
            ->whereIn('player_id', $playerIds)
            ->whereIn('magic_skill_id', $magicSkillIds)
            ->delete();
    }

    private function currentMagicSkillIds(Clan $clan): Collection
    {
        return $clan->learnedSkills()
            ->with('definition.levels')
            ->get()
            ->map(function ($learned) {
                return $learned->definition->levels
                    ->firstWhere('level', $learned->current_level)
                    ?->magic_skill_id;
            })
            ->filter()
            ->unique()
            ->values();
    }
}
