<?php

declare(strict_types=1);

namespace App\Modules\Location\Application\UseCases;

use App\Modules\Location\Application\DTOs\TakeItemsPageDTO;
use App\Modules\Location\Application\Mappers\TakeItemsPageViewMapper;
use App\Modules\Location\Domain\Contracts\LocationReadRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class GetTakeItemsPage
{
    public function __construct(
        private readonly LocationReadRepository $readRepository,
        private readonly TakeItemsPageViewMapper $mapper,
    ) {}

    public function execute(User $user): TakeItemsPageDTO
    {
        return $this->mapper->map(
            $this->readRepository->getItemsOnLocation($user, $user->location_id),
        );
    }
}
