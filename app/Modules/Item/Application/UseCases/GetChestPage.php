<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\UseCases;

use App\Modules\Item\Application\DTOs\ChestPageDTO;
use App\Modules\Item\Application\Mappers\ChestPageViewMapper;
use App\Modules\Item\Domain\Contracts\ItemReadRepository;

class GetChestPage
{
    public function __construct(
        private readonly ItemReadRepository $readRepository,
        private readonly ChestPageViewMapper $mapper,
    ) {}

    public function execute(int $chestId, string $message = ''): ChestPageDTO
    {
        return $this->mapper->map(
            $this->readRepository->findChestWithItems($chestId),
            $message,
        );
    }
}
