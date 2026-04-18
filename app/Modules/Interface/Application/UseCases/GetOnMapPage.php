<?php

declare(strict_types=1);

namespace App\Modules\Interface\Application\UseCases;

use App\Modules\Interface\Application\DTOs\OnMapPageDTO;
use App\Modules\Interface\Domain\Contracts\InterfaceReadRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class GetOnMapPage
{
    public function __construct(
        private readonly InterfaceReadRepository $readRepository,
    ) {}

    public function execute(?string $slug, User $user): OnMapPageDTO
    {
        if ($slug !== null && $slug !== '') {
            $map = $this->readRepository->findMapBySlug($slug);
            $view = $map ? sprintf('maps.%s.frame', $map->folder) : 'maps.city.frame';
        } else {
            $view = sprintf('maps.%s.frame', $user->currentLocation->map->folder);
        }

        if (! $this->readRepository->viewExists($view)) {
            $view = 'maps.city.frame';
        }

        return new OnMapPageDTO($view);
    }
}
