<?php

declare(strict_types=1);

namespace App\Modules\Backpack\Infrastructure\Persistence;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Backpack\Domain\Repositories\BackpackRepositoryInterface;

class EloquentBackpackRepository implements BackpackRepositoryInterface
{
    public function updateOrder(int $userId, array $ids): void
    {
        foreach ($ids as $index => $backpackId) {
            Backpack::where('id', (int) $backpackId)
                ->where('user_id', $userId)
                ->update(['sort_order' => $index]);
        }
    }
}
