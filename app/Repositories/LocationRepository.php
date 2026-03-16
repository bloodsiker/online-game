<?php

namespace App\Repositories;

use App\Models\Location\Location;

class LocationRepository extends AbstractRepository
{
    public function getModelClass(): string
    {
        return Location::class;
    }

    private function query()
    {
        return $this->model->query();
    }

    public function getQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->model->query()->select(['locations.*']);
    }
}
