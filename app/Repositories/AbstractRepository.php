<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

abstract class AbstractRepository implements RepositoryInterface
{
    protected Model $model;

    abstract public function getModelClass(): string;

    public function __construct()
    {
        $this->model = app($this->getModelClass());
    }

    public function getOneById(int|string $id): ?Model
    {
        return $this->model->find($id);
    }

    public function getByIds(array $ids): Collection
    {
        return $this->model->whereIn($this->model->getKeyName(), $ids)->get();
    }

    public function getAll(): Collection
    {
        return $this->model->all();
    }

    public function getQuery(): Builder
    {
        return $this->model->query();
    }

    public function getCount(): int
    {
        return $this->model->count();
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function update(array $data, int|string $id): Model
    {
        $model = $this->model->findOrFail($id);
        $model->update($data);

        return $model;
    }

    /**
     * @throws ModelNotFoundException
     */
    public function delete(int|string $id): void
    {
        $this->model->findOrFail($id)->delete();
    }

    public function deleteByIds(array $ids): void
    {
        $this->model->whereIn($this->model->getKeyName(), $ids)->delete();
    }

    /**
     * @throws ModelNotFoundException
     */
    public function copyByIds(array $ids): void
    {
        foreach ($ids as $id) {
            $this->model->findOrFail($id)->replicate()->save();
        }
    }
}