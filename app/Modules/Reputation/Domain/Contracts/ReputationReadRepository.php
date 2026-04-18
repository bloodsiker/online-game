<?php

declare(strict_types=1);

namespace App\Modules\Reputation\Domain\Contracts;

use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\Reputation\Infrastructure\Persistence\Models\ReputationShopItem;
use Illuminate\Support\Collection;

interface ReputationReadRepository
{
    public function getAllReputations(): Collection;

    public function findReputationForIndexOrFail(int $id): Reputation;

    public function findReputationForShopOrFail(int $id): Reputation;

    public function findShopItemOrFail(int $reputationId, int $itemId): ReputationShopItem;
}
