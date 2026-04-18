<?php

declare(strict_types=1);

namespace App\Modules\Interface\Application\UseCases;

use App\Modules\Interface\Application\DTOs\HeroPageDTO;
use App\Modules\Interface\Application\Mappers\HeroPageViewMapper;
use App\Modules\Interface\Domain\Contracts\InterfaceReadRepository;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class GetHeroPage
{
    public function __construct(
        private readonly InterfaceReadRepository $readRepository,
        private readonly PlayerStatService $statService,
        private readonly HeroPageViewMapper $mapper,
    ) {}

    public function execute(User $user): HeroPageDTO
    {
        $player = $user->player;

        return $this->mapper->map(
            $player,
            $this->statService->resolve($player),
            $this->readRepository->getPlayerActiveEffects($player->id),
        );
    }
}
