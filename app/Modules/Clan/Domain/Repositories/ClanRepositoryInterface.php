<?php

declare(strict_types=1);

namespace App\Modules\Clan\Domain\Repositories;

use App\Modules\Clan\Domain\Models\Clan;

interface ClanRepositoryInterface
{
    public function create(array $data): Clan;
}
