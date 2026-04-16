<?php

declare(strict_types=1);

namespace App\Modules\Backpack\Domain\Repositories;

interface BackpackRepositoryInterface
{
    public function updateOrder(int $userId, array $ids): void;
}