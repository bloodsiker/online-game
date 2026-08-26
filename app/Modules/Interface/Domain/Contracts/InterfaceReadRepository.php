<?php

declare(strict_types=1);

namespace App\Modules\Interface\Domain\Contracts;

use App\Modules\Location\Infrastructure\Persistence\Models\Map;
use Carbon\Carbon;
use Illuminate\Support\Collection;

interface InterfaceReadRepository
{
    public function findMapBySlug(string $slug): ?Map;

    public function viewExists(string $view): bool;

    public function getUsersOnLocation(int $locationId): Collection;

    public function getOnlineUsers(Carbon $threshold): Collection;

    public function getPlayerActiveEffects(int $playerId): Collection;
}
