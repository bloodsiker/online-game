<?php

declare(strict_types=1);

namespace App\Modules\Interface\Application\UseCases;

use App\Modules\Interface\Application\DTOs\OnMapPageDTO;
use App\Modules\Interface\Domain\Contracts\InterfaceReadRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class GetOnMapPage
{
    /** Карта id=1 — folder='city/main' (см. таблицу maps); используется как запасной вид */
    private const DEFAULT_VIEW = 'maps.city.main.frame';

    public function __construct(
        private readonly InterfaceReadRepository $readRepository,
    ) {}

    public function execute(?string $slug, ?User $user): OnMapPageDTO
    {
        if ($slug !== null && $slug !== '') {
            $map = $this->readRepository->findMapBySlug($slug);
            $view = $map ? sprintf('maps.%s.frame', $map->folder) : self::DEFAULT_VIEW;
        } elseif ($user !== null) {
            $view = sprintf('maps.%s.frame', $user->currentLocation->map->folder);
        } else {
            // Гость без ?s= (например, прямой заход на /on-map без слага) — карты не знаем, показываем город
            $view = self::DEFAULT_VIEW;
        }

        if (! $this->readRepository->viewExists($view)) {
            $view = self::DEFAULT_VIEW;
        }

        return new OnMapPageDTO($view);
    }
}
