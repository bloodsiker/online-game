<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\UseCases;

use App\Modules\Structure\Blacksmith\Application\DTOs\KraftPageDTO;
use App\Modules\Structure\Blacksmith\Application\Mappers\KraftPageViewMapper;
use App\Modules\Structure\Blacksmith\Domain\Contracts\BlacksmithReadRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class GetKraftPage
{
    public function __construct(
        private readonly BlacksmithReadRepository $readRepository,
        private readonly KraftPageViewMapper $mapper,
    ) {}

    public function execute(User $user, int $blacksmithId): KraftPageDTO
    {
        return $this->mapper->map(
            blacksmith: $this->readRepository->findStructureOrFail($blacksmithId),
            recipes: $this->readRepository->getCraftRecipes($user),
            resources: $this->readRepository->getResourceCounts($user),
        );
    }
}
