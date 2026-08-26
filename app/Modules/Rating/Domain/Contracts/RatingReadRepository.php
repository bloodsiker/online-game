<?php

declare(strict_types=1);

namespace App\Modules\Rating\Domain\Contracts;

use App\Modules\Skill\Infrastructure\Persistence\Models\Skill;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface RatingReadRepository
{
    /**
     * @return Collection<int, Skill>
     */
    public function getSkills(): Collection;

    public function paginateLevelRating(int $perPage): LengthAwarePaginator;

    public function paginateVictoriesRating(int $perPage): LengthAwarePaginator;

    public function paginateDeathsRating(int $perPage): LengthAwarePaginator;

    public function paginateWealthRating(int $perPage): LengthAwarePaginator;

    public function paginateSkillRating(int $skillId, int $perPage): LengthAwarePaginator;

    public function findLevelRatingPosition(string $nick): ?int;

    public function findVictoriesRatingPosition(string $nick): ?int;

    public function findDeathsRatingPosition(string $nick): ?int;

    public function findWealthRatingPosition(string $nick): ?int;

    public function findSkillRatingPosition(int $skillId, string $nick): ?int;
}
