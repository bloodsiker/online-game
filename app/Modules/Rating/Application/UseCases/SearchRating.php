<?php

declare(strict_types=1);

namespace App\Modules\Rating\Application\UseCases;

use App\Modules\Rating\Application\DTOs\RatingSearchResultDTO;
use App\Modules\Rating\Domain\Contracts\RatingReadRepository;

class SearchRating
{
    public function __construct(private readonly RatingReadRepository $ratingReadRepository) {}

    public function execute(string $nick, string $type): RatingSearchResultDTO
    {
        $nick = trim($nick);

        if ($nick === '') {
            return new RatingSearchResultDTO(false, error: 'Введите ник игрока.');
        }

        $perPage = 40;
        if (str_starts_with($type, 'skill_')) {
            $skillId = (int) str_replace('skill_', '', $type);
            $position = $this->ratingReadRepository->findSkillRatingPosition($skillId, $nick);
        } elseif (str_starts_with($type, 'influence_')) {
            $mapId = (int) str_replace('influence_', '', $type);
            $position = $this->ratingReadRepository->findMapInfluenceRatingPosition($mapId, $nick);
        } elseif (str_starts_with($type, 'boss_')) {
            $monsterId = (int) str_replace('boss_', '', $type);
            $position = $this->ratingReadRepository->findBossDefeatRatingPosition($monsterId, $nick);
        } else {
            $position = match ($type) {
                'victories' => $this->ratingReadRepository->findVictoriesRatingPosition($nick),
                'deaths' => $this->ratingReadRepository->findDeathsRatingPosition($nick),
                'wealth' => $this->ratingReadRepository->findWealthRatingPosition($nick),
                'reputation' => $this->ratingReadRepository->findReputationRatingPosition($nick),
                default => $this->ratingReadRepository->findLevelRatingPosition($nick),
            };
        }

        if ($position === null) {
            return new RatingSearchResultDTO(false, error: "Игрок «{$nick}» не найден в рейтинге.");
        }

        return new RatingSearchResultDTO(
            true,
            page: (int) ceil($position / $perPage),
            position: $position,
        );
    }
}
