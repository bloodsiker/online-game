<?php

namespace App\Repositories;

use App\Models\Monster\Monster;

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

    public function getQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->model->query()->select(['monsters.*']);
    }
}
