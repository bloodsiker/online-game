<?php

declare(strict_types=1);

namespace App\Modules\Player\Application\UseCases;

use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerGatheringStat;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerSkill;
use App\Modules\Share\Domain\Enums\RecipeUnlockType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareRecipe;
use App\Modules\Skill\Infrastructure\Persistence\Models\Skill;
use App\Modules\Skill\Infrastructure\Persistence\Models\SkillLevelRequirement;

class GetProfessionsPage
{
    private const PROFESSION_ORDER = [
        'Алхимик',
        'Повар',
        'Ремесленник',
        'Кузнец',
        'Травник',
        'Рыбак',
        'Геолог',
        'Лесоруб',
    ];

    /** Профессии добычи ресурсов — у них нет рецептов, вместо этого показываем счётчик добытого (см. player_gathering_stats). */
    private const GATHERING_PROFESSIONS = ['Травник', 'Рыбак', 'Геолог', 'Лесоруб'];

    /**
     * @return array{professions: list<array<string, mixed>>, activeProfessionId: ?int}
     */
    public function execute(Player $player, ?int $requestedProfessionId = null): array
    {
        $skills = Skill::query()
            ->where('type', 'peaceful')
            ->whereIn('name', self::PROFESSION_ORDER)
            ->get()
            ->sortBy(function (Skill $skill): string {
                $position = array_search($skill->name, self::PROFESSION_ORDER, true);

                return sprintf('%03d-%s', $position === false ? 999 : $position, $skill->name);
            })
            ->values();

        $skillIds = $skills->modelKeys();
        $progressBySkill = PlayerSkill::query()
            ->where('player_id', $player->id)
            ->whereIn('skill_id', $skillIds)
            ->get()
            ->keyBy('skill_id');
        $firstRequirements = SkillLevelRequirement::query()
            ->whereIn('skill_id', $skillIds)
            ->where('lvl', 1)
            ->get()
            ->keyBy('skill_id');
        $gatheringSkillIds = $skills->filter(fn (Skill $skill): bool => in_array($skill->name, self::GATHERING_PROFESSIONS, true))->modelKeys();
        $gatheredResourcesBySkill = ShareItem::query()
            ->whereIn('skill_id', $gatheringSkillIds)
            ->get(['id', 'name', 'image', 'transparent_image', 'skill_id'])
            ->groupBy('skill_id');
        $gatheringStatsByShareItem = PlayerGatheringStat::query()
            ->where('player_id', $player->id)
            ->get()
            ->keyBy('share_item_id');
        $recipesBySkill = $player->recipes()
            ->where('share_recipes.unlock_type', RecipeUnlockType::LEARNABLE->value)
            ->whereNotNull('share_recipes.kraft_item_id')
            ->whereHas('itemInfo.skill', fn ($query) => $query->where('type', 'peaceful'))
            ->with(['itemInfo.skill', 'kraftItem'])
            ->get()
            ->groupBy(fn (ShareRecipe $recipe): int => (int) $recipe->itemInfo->skill_id);

        $professions = $skills->map(function (Skill $skill) use ($progressBySkill, $firstRequirements, $recipesBySkill, $gatheredResourcesBySkill, $gatheringStatsByShareItem): array {
            $progress = $progressBySkill->get($skill->id);
            $firstRequirement = $firstRequirements->get($skill->id);
            $level = (int) ($progress?->lvl ?? 1);
            $experience = (int) ($progress?->exp ?? 0);
            $experienceToLevel = max(1, (int) ($progress?->exp_diff ?? $firstRequirement?->exp_diff ?? $firstRequirement?->exp_required ?? 100));
            $levelStartExperience = max(0, (int) ($progress?->exp_up ?? $firstRequirement?->exp_required ?? 100) - $experienceToLevel);
            $levelExperience = max(0, $experience - $levelStartExperience);
            $recipes = $recipesBySkill->get($skill->id, collect())
                ->sortBy(fn (ShareRecipe $recipe): string => (string) $recipe->itemInfo->name)
                ->map(fn (ShareRecipe $recipe): array => [
                    'id' => (int) $recipe->id,
                    'shareItemId' => (int) $recipe->share_item_id,
                    'name' => (string) $recipe->itemInfo->name,
                    'description' => (string) ($recipe->itemInfo->description ?? ''),
                    'image' => (string) $recipe->itemInfo->image,
                    'requiredLevel' => max(1, (int) $recipe->itemInfo->skill_lvl),
                    'experience' => max(1, (int) $recipe->itemInfo->skill_exp),
                    'resultShareItemId' => (int) $recipe->kraftItem->id,
                    'resultName' => (string) $recipe->kraftItem->name,
                    'resultImage' => (string) $recipe->kraftItem->image,
                    'learnedAt' => $recipe->pivot?->created_at?->format('d.m.Y'),
                ])
                ->values()
                ->all();

            $isGathering = in_array($skill->name, self::GATHERING_PROFESSIONS, true);

            $gatheredResources = [];
            if ($isGathering) {
                $gatheredResources = $gatheredResourcesBySkill->get($skill->id, collect())
                    ->map(function (ShareItem $resource) use ($gatheringStatsByShareItem): array {
                        return [
                            'shareItemId' => (int) $resource->id,
                            'name' => (string) $resource->name,
                            'image' => (string) ($resource->transparent_image ?? $resource->image),
                            'totalGathered' => (int) ($gatheringStatsByShareItem->get($resource->id)?->total_gathered ?? 0),
                        ];
                    })
                    ->filter(fn (array $resource): bool => $resource['totalGathered'] > 0)
                    ->sortByDesc('totalGathered')
                    ->values()
                    ->all();
            }
            $totalGathered = array_sum(array_column($gatheredResources, 'totalGathered'));

            return [
                'id' => (int) $skill->id,
                'name' => $skill->name === 'Ремесленник' ? 'Ремесник' : (string) $skill->name,
                'description' => (string) ($skill->description ?? ''),
                'level' => $level,
                'experiencePercent' => min(100, round($levelExperience * 100 / $experienceToLevel, 1)),
                'levelExperience' => $levelExperience,
                'levelExperienceRequired' => $experienceToLevel,
                'recipes' => $recipes,
                'recipesCount' => count($recipes),
                'isGathering' => $isGathering,
                'gatheredResources' => $gatheredResources,
                'totalGathered' => $totalGathered,
            ];
        })->all();

        $professionIds = array_column($professions, 'id');
        $activeProfessionId = in_array($requestedProfessionId, $professionIds, true)
            ? $requestedProfessionId
            : (collect($professions)->first(fn (array $profession): bool => $profession['recipesCount'] > 0)['id'] ?? $professionIds[0] ?? null);

        return compact('professions', 'activeProfessionId');
    }
}
