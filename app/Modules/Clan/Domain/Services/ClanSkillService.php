<?php

declare(strict_types=1);

namespace App\Modules\Clan\Domain\Services;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Clan\Domain\Enums\ClanLogAction;
use App\Modules\Clan\Domain\Models\Clan;
use App\Modules\Clan\Domain\Models\ClanLearnedSkill;
use App\Modules\Clan\Domain\Models\ClanLog;
use App\Modules\Clan\Domain\Models\ClanSkillDefinition;
use App\Modules\Clan\Domain\Models\ClanSkillLevel;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerMagicSkill;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ClanSkillService
{
    /**
     * Attempt to learn (or upgrade) a clan skill.
     * Returns null on success, or an error message string.
     */
    public function learn(Clan $clan, ClanSkillDefinition $definition, Player $player): ?string
    {
        $learned = $clan->learnedSkills()->where('clan_skill_definition_id', $definition->id)->first();
        $currentLevel = $learned?->current_level ?? 0;
        $nextLevel = $currentLevel + 1;

        if ($nextLevel > $definition->max_level) {
            return 'Навык уже достиг максимального уровня.';
        }

        $levelData = $definition->levels()->where('level', $nextLevel)->first();
        if (! $levelData) {
            return 'Данные уровня навыка не найдены.';
        }

        if ($clan->lvl < $levelData->required_clan_level) {
            return "Недостаточный уровень клана. Требуется: {$levelData->required_clan_level}.";
        }

        if ($clan->points < $levelData->required_bonus_points) {
            return "Недостаточно бонусных очков. Требуется: {$levelData->required_bonus_points}.";
        }

        $itemRequirements = $this->itemRequirements($levelData);
        if ($error = $this->validateItemRequirements($player, $itemRequirements)) {
            return $error;
        }

        try {
            DB::transaction(function () use ($clan, $definition, $levelData, $learned, $nextLevel, $currentLevel, $player, $itemRequirements) {
                $clan->decrement('points', $levelData->required_bonus_points);
                $this->consumeItemRequirements($player, $itemRequirements);

                if ($learned) {
                    $learned->update(['current_level' => $nextLevel]);
                } else {
                    $learned = ClanLearnedSkill::create([
                        'clan_id' => $clan->id,
                        'clan_skill_definition_id' => $definition->id,
                        'current_level' => $nextLevel,
                    ]);
                }

                $this->syncSkillForAllMembers($clan, $definition, $currentLevel, $nextLevel, $levelData);

                $isNew = $currentLevel === 0;
                ClanLog::create([
                    'clan_id' => $clan->id,
                    'user_id' => $player->user_id,
                    'action' => $isNew ? ClanLogAction::SKILL_LEARNED : ClanLogAction::SKILL_UPGRADED,
                    'details' => $isNew
                        ? "Изучен новый навык «{$definition->name}»"
                        : "Изучен уровень {$nextLevel} навыка «{$definition->name}»",
                ]);
            });
        } catch (\RuntimeException $e) {
            return $e->getMessage();
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

        if ($requirements->isNotEmpty() || $levelData->share_item_id === null) {
            return $requirements;
        }

        return collect([[
            'share_item_id' => (int) $levelData->share_item_id,
            'count' => (int) ($levelData->share_item_count ?? 1),
            'name' => $levelData->stoneItem?->name ?? 'Предмет',
        ]]);
    }

    private function validateItemRequirements(Player $player, Collection $requirements): ?string
    {
        $available = $this->backpacksByShareItem($player, $requirements);

        foreach ($requirements as $requirement) {
            if ($available->get($requirement['share_item_id'], collect())->sum('count') < $requirement['count']) {
                return "В рюкзаке недостаточно предметов «{$requirement['name']}». Нужно: {$requirement['count']}.";
            }
        }

        return null;
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
            ->with('item');

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
}
