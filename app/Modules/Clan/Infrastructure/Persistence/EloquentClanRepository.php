<?php

declare(strict_types=1);

namespace App\Modules\Clan\Infrastructure\Persistence;

use App\Modules\Clan\Domain\Models\Clan;
use App\Modules\Clan\Domain\Repositories\ClanRepositoryInterface;

class EloquentClanRepository implements ClanRepositoryInterface
{
    public function create(array $data): Clan
    {
        /** @var Clan $clan */
        $clan = Clan::create($data);

        return $clan;
    }
}
