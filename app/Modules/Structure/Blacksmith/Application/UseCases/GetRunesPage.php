<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\UseCases;

use App\Modules\Structure\Blacksmith\Application\DTOs\RunePageDTO;
use App\Modules\Structure\Blacksmith\Application\Mappers\RunePageViewMapper;
use App\Modules\Structure\Blacksmith\Domain\Contracts\BlacksmithReadRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class GetRunesPage
{
    public function __construct(
        private readonly BlacksmithReadRepository $readRepository,
        private readonly RunePageViewMapper $mapper,
    ) {}

    public function execute(User $user, int $blacksmithId): RunePageDTO
    {
        return $this->mapper->map(
            blacksmith: $this->readRepository->findStructureOrFail($blacksmithId),
            items: $this->readRepository->getImbueableItems($user),
            runes: $this->readRepository->getRunes($user),
            runeKeys: $this->readRepository->getRuneKeys($user),
        );
    }
}
