<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\UseCases;

use App\Models\User;
use App\Modules\Structure\Blacksmith\Application\DTOs\UpgradePageDTO;
use App\Modules\Structure\Blacksmith\Application\Mappers\UpgradePageViewMapper;
use App\Modules\Structure\Blacksmith\Domain\Contracts\BlacksmithReadRepository;

class GetUpgradePage
{
    public function __construct(
        private readonly BlacksmithReadRepository $readRepository,
        private readonly UpgradePageViewMapper $mapper,
    ) {}

    public function execute(User $user, int $blacksmithId): UpgradePageDTO
    {
        return $this->mapper->map(
            blacksmith: $this->readRepository->findStructureOrFail($blacksmithId),
            player: $user->player,
            items: $this->readRepository->getUpgradeableItems($user),
            baseScrolls: $this->readRepository->getBaseScrolls($user),
            bonusScrolls: $this->readRepository->getBonusScrolls($user),
        );
    }
}
