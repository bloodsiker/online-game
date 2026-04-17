<?php

declare(strict_types=1);

namespace App\Modules\Backpack\Application\UseCases;

use App\Modules\Backpack\Domain\Repositories\BackpackRepositoryInterface;

class UpdateOrder
{
    public function __construct(
        private readonly BackpackRepositoryInterface $repository,
    ) {}

    public function execute(int $userId, array $ids): void
    {
        $this->repository->updateOrder($userId, $ids);
    }
}
