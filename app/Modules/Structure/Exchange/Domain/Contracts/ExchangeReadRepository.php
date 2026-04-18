<?php

declare(strict_types=1);

namespace App\Modules\Structure\Exchange\Domain\Contracts;

use App\Modules\Structure\Exchange\Infrastructure\Persistence\Models\Exchange;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use Illuminate\Support\Collection;

interface ExchangeReadRepository
{
    public function findStructureOrFail(int $id): Structure;

    /**
     * @return Collection<int, Exchange>
     */
    public function getExchangeItems(int $structureId): Collection;

    public function findExchangeItem(int $structureId, int $fromShareItemId, int $toShareItemId): ?Exchange;
}
