<?php

declare(strict_types=1);

namespace App\Modules\Rating\Application\UseCases;

use App\Modules\Rating\Application\DTOs\RatingPageDTO;
use App\Modules\Rating\Application\Mappers\RatingPageViewMapper;
use App\Modules\Rating\Domain\Contracts\RatingReadRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class GetRatingPage
{
    public function __construct(
        private readonly RatingReadRepository $ratingReadRepository,
        private readonly RatingPageViewMapper $mapper,
    ) {}

    /**
     * @return array{page: RatingPageDTO}
     */
    public function execute(User $user, string $type): array
    {
        $menu = [
            'level' => [
                'title' => 'По уровню',
                'name' => 'Опыт',
                'column' => 'exp',
            ],
            'victories' => [
                'title' => 'По победам',
                'name' => 'Победы',
                'column' => 'victory',
            ],
            'deaths' => [
                'title' => 'По поражениям',
                'name' => 'Поражения',
                'column' => 'death',
            ],
            'wealth' => [
                'title' => 'По богатству',
                'name' => 'Монеты',
                'column' => 'user.money',
            ],
            'reputation' => [
                'title' => 'По репутационному рейтингу',
                'name' => 'Репутационный рейтинг',
                'column' => 'reputation_rating',
            ],
        ];

        foreach ($this->ratingReadRepository->getInfluenceMaps() as $map) {
            $menu['influence_'.$map->id] = [
                'title' => $map->name,
                'name' => 'Влияние',
                'column' => 'map_influence',
            ];
        }

        foreach ($this->ratingReadRepository->getDefeatedBosses() as $boss) {
            $menu['boss_'.$boss->id] = [
                'title' => $boss->name,
                'name' => 'Побед над боссом',
                'column' => 'boss_defeats',
            ];
        }

        foreach ($this->ratingReadRepository->getSkills() as $skill) {
            $menu['skill_'.$skill->id] = [
                'title' => $skill->name,
                'name' => 'Уровень навыка',
                'column' => 'lvl',
                'skill_group' => $skill->type === 'peaceful' ? 'peaceful' : 'combat',
            ];
        }

        if (! array_key_exists($type, $menu)) {
            $type = array_key_first($menu);
        }

        $isSkillRating = str_starts_with($type, 'skill_');
        $isMapInfluenceRating = str_starts_with($type, 'influence_');
        $isBossDefeatRating = str_starts_with($type, 'boss_');

        if ($isSkillRating) {
            $skillId = (int) str_replace('skill_', '', $type);
            $players = $this->ratingReadRepository->paginateSkillRating($skillId, 40);
        } elseif ($isMapInfluenceRating) {
            $mapId = (int) str_replace('influence_', '', $type);
            $players = $this->ratingReadRepository->paginateMapInfluenceRating($mapId, 40);
        } elseif ($isBossDefeatRating) {
            $monsterId = (int) str_replace('boss_', '', $type);
            $players = $this->ratingReadRepository->paginateBossDefeatRating($monsterId, 40);
        } else {
            $players = match ($type) {
                'victories' => $this->ratingReadRepository->paginateVictoriesRating(40),
                'deaths' => $this->ratingReadRepository->paginateDeathsRating(40),
                'wealth' => $this->ratingReadRepository->paginateWealthRating(40),
                'reputation' => $this->ratingReadRepository->paginateReputationRating(40),
                default => $this->ratingReadRepository->paginateLevelRating(40),
            };
        }

        return [
            'page' => $this->mapper->map($user, $menu, $type, $isSkillRating, $players),
        ];
    }
}
