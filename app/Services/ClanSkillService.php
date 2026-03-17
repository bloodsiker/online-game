<?php

namespace App\Services;

use App\Models\Clan\Clan;
use App\Models\Clan\ClanLearnedSkill;
use App\Models\Clan\ClanSkillDefinition;
use App\Models\Clan\ClanSkillLevel;
use App\Models\Backpack;
use App\Models\Player\Player;
use App\Models\Player\PlayerMagicSkill;
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
        if (!$levelData) {
            return 'Данные уровня навыка не найдены.';
        }

        if ($clan->lvl < $levelData->required_clan_level) {
            return "Недостаточный уровень клана. Требуется: {$levelData->required_clan_level}.";
        }

        if ($clan->bonus_points < $levelData->required_bonus_points) {
            return "Недостаточно бонусных очков. Требуется: {$levelData->required_bonus_points}.";
        }

        // Check stone in player's backpack
        if ($levelData->stone_share_item_id) {
            $stone = Backpack::where('user_id', $player->user_id)
                ->whereHas('item', fn($q) => $q->where('share_item_id', $levelData->stone_share_item_id))
                ->first();

            if (!$stone) {
                $stoneName = $levelData->stoneItem?->name ?? 'Камень';
                return "В рюкзаке нет необходимого предмета: {$stoneName}.";
            }
        }

        DB::transaction(function () use ($clan, $definition, $levelData, $learned, $nextLevel, $currentLevel, $player) {
            // Consume bonus points
            $clan->decrement('bonus_points', $levelData->required_bonus_points);

            // Consume stone from player's backpack
            if ($levelData->stone_share_item_id) {
                $stone = Backpack::where('user_id', $player->user_id)
                    ->whereHas('item', fn($q) => $q->where('share_item_id', $levelData->stone_share_item_id))
                    ->first();

                if ($stone->count <= 1) {
                    $stone->delete();
                } else {
                    $stone->decrement('count');
                }
            }

            // Update or create learned skill record
            if ($learned) {
                $learned->update(['current_level' => $nextLevel]);
            } else {
                $learned = ClanLearnedSkill::create([
                    'clan_id'                   => $clan->id,
                    'clan_skill_definition_id'  => $definition->id,
                    'current_level'             => $nextLevel,
                ]);
            }

            // Sync magic skills for all clan members
            $this->syncSkillForAllMembers($clan, $definition, $currentLevel, $nextLevel, $levelData);
        });

        return null;
    }

    /**
     * When a skill level changes, update player_magic_skills for all clan members.
     */
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

        // Remove old level's magic skill if it existed
        if ($oldLevel > 0) {
            $oldLevelData = $definition->levels()->where('level', $oldLevel)->first();
            if ($oldLevelData?->magic_skill_id) {
                PlayerMagicSkill::whereIn('player_id', $playerIds)
                    ->where('magic_skill_id', $oldLevelData->magic_skill_id)
                    ->delete();
            }
        }

        // Add new level's magic skill
        if ($newLevelData->magic_skill_id) {
            $rows = $playerIds->map(fn($playerId) => [
                'player_id'      => $playerId,
                'magic_skill_id' => $newLevelData->magic_skill_id,
                'is_equipped'    => true,
            ])->toArray();

            PlayerMagicSkill::upsert($rows, ['player_id', 'magic_skill_id'], ['is_equipped']);
        }
    }

    /**
     * Add all current clan skills to a player (called on join).
     */
    public function applyAllSkillsToPlayer(Player $player, Clan $clan): void
    {
        $learnedSkills = $clan->learnedSkills()->with('definition.levels')->get();

        foreach ($learnedSkills as $learned) {
            $levelData = $learned->definition->levels
                ->firstWhere('level', $learned->current_level);

            if ($levelData?->magic_skill_id) {
                PlayerMagicSkill::firstOrCreate(
                    ['player_id' => $player->id, 'magic_skill_id' => $levelData->magic_skill_id],
                    ['is_equipped' => true],
                );
            }
        }
    }

    /**
     * Remove all clan skills from a player (called on leave/kick).
     */
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