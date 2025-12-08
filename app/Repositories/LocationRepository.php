<?php

namespace App\Repositories;

use App\Models\Location;

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

    public function getQuery()
    {
        $query = $this->model->query();

        return $query->select(['locations.*']);
    }
}
