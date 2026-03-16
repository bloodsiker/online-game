<?php

namespace App\Repositories;

use App\Models\Clan\Clan;

class ClanRepository extends AbstractRepository
{
    public function getModelClass(): string
    {
        return Clan::class;
    }
}
