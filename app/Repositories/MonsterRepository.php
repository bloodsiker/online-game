<?php

namespace App\Repositories;

use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use Illuminate\Database\Eloquent\Builder;

class MonsterRepository extends AbstractRepository
{
    public function getModelClass(): string
    {
        return Monster::class;
    }

    private function query()
    {
        return $this->model->query();
    }

    public function getQuery(): Builder
    {
        return $this->model->query()->select(['monsters.*']);
    }
}
