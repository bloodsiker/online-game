<?php

declare(strict_types=1);

namespace App\Modules\Structure\Warehouse\Application\UseCases;

use App\Models\Warehouse;

class GetWarehouseCount
{
    public function execute(int $userId, int $structureId): int
    {
        return Warehouse::where('user_id', $userId)
            ->where('structure_id', $structureId)
            ->count();
    }
}
