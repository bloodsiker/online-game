<?php

declare(strict_types=1);

namespace App\Modules\Interface\Application\UseCases;

use App\Modules\Interface\Application\DTOs\WhoPageDTO;
use App\Modules\Interface\Application\Mappers\WhoPageViewMapper;
use App\Modules\Interface\Domain\Contracts\InterfaceReadRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Carbon\Carbon;

class GetWhoPage
{
    public function __construct(
        private readonly InterfaceReadRepository $readRepository,
        private readonly WhoPageViewMapper $mapper,
    ) {}

    public function execute(User $user): WhoPageDTO
    {
        $threshold = Carbon::now()->subMinutes(10);

        return $this->mapper->map(
            $this->readRepository->getUsersOnLocation($user->location_id),
            $this->readRepository->getOnlineUsers($threshold),
            $threshold,
        );
    }
}
