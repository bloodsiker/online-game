<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\UseCases;

use App\Modules\Structure\Blacksmith\Application\DTOs\BreakPageDTO;
use App\Modules\Structure\Blacksmith\Application\Mappers\BreakPageViewMapper;
use App\Modules\Structure\Blacksmith\Domain\Contracts\BlacksmithReadRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class GetBreakPage
{
    public function __construct(
        private readonly BlacksmithReadRepository $readRepository,
        private readonly BreakPageViewMapper $mapper,
    ) {}

    public function execute(User $user, int $blacksmithId): BreakPageDTO
    {
        return $this->mapper->map(
            blacksmith: $this->readRepository->findStructureOrFail($blacksmithId),
            items: $this->readRepository->getBreakableItems($user),
            crystal: $this->readRepository->findCrystalOrFail(),
        );
    }
}
