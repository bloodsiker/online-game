<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\UseCases;

use App\Modules\Structure\Blacksmith\Application\DTOs\UpgradeTransferPageDTO;
use App\Modules\Structure\Blacksmith\Application\Mappers\UpgradeTransferPageViewMapper;
use App\Modules\Structure\Blacksmith\Domain\Contracts\BlacksmithReadRepository;
use App\Modules\Structure\Blacksmith\Domain\Services\UpgradeTransferService;
use App\Modules\User\Infrastructure\Persistence\Models\User;

final readonly class GetUpgradeTransferPage
{
    public function __construct(
        private BlacksmithReadRepository $readRepository,
        private UpgradeTransferPageViewMapper $mapper,
    ) {}

    public function execute(User $user, int $blacksmithId): UpgradeTransferPageDTO
    {
        return $this->mapper->map(
            blacksmith: $this->readRepository->findStructureOrFail($blacksmithId),
            items: $this->readRepository->getUpgradeableItems($user),
            transgressors: $this->readRepository->getOwnedItemsByName($user, UpgradeTransferService::REQUIRED_ITEM_NAME),
        );
    }
}
