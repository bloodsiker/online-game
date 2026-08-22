<?php

declare(strict_types=1);

namespace App\Modules\Monster\Application\UseCases;

use App\Modules\Monster\Application\DTOs\MonsterInfoPageDTO;
use App\Modules\Monster\Domain\Contracts\MonsterReadRepository;

class GetMonsterInfoPage
{
    public function __construct(
        private readonly MonsterReadRepository $readRepository,
    ) {}

    public function execute(int $locationMonsterId): MonsterInfoPageDTO
    {
        $monster = $this->readRepository->findByLocationMonsterId($locationMonsterId);
        abort_if($monster === null, 404);

        $habitatMaps = $monster->locations
            ->pluck('map')
            ->filter()
            ->unique('id')
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->pluck('name')
            ->values()
            ->all();

        return new MonsterInfoPageDTO($monster, $habitatMaps);
    }
}
