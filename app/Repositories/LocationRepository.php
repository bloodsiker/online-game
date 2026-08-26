<?php

namespace App\Repositories;

use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use Illuminate\Database\Eloquent\Builder;

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

    public function getQuery(): Builder
    {
        return $this->model->query()->select(['locations.*']);
    }
}
