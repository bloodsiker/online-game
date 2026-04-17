<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\UseCases;

use App\Models\User;
use App\Modules\Structure\Blacksmith\Application\DTOs\GemPageDTO;
use App\Modules\Structure\Blacksmith\Application\Mappers\GemPageViewMapper;
use App\Modules\Structure\Blacksmith\Domain\Contracts\BlacksmithReadRepository;

class GetGemsPage
{
    public function __construct(
        private readonly BlacksmithReadRepository $readRepository,
        private readonly GemPageViewMapper $mapper,
    ) {}

    public function execute(User $user, int $blacksmithId): GemPageDTO
    {
        return $this->mapper->map(
            blacksmith: $this->readRepository->findStructureOrFail($blacksmithId),
            items: $this->readRepository->getSocketableItems($user),
            gems: $this->readRepository->getGems($user),
            socketKits: $this->readRepository->getSocketKits($user),
        );
    }
}
