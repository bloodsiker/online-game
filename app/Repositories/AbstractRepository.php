<?php

namespace App\Repositories;

use Exception;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class AbstractRepository implements RepositoryInterface
{
    /**
     * @var Model
     */
    protected Model $model;

    protected $auth;

    abstract public function getModelClass(): string;

    public function setModel($modelPath): void
    {
        $this->model = app($modelPath);
    }

    public function __construct()
    {
        $this->setModel($this->getModelClass());
        try {
            $this->auth = app(Guard::class);
        } catch (Exception $e) {

        }
    }

    public function getOneById($id): ?Model
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

    public function getQuery()
    {
        return $this->model->query();
    }

    public function delete($id): void
    {
        $item = $this->model->find($id);
        $item->delete();
    }

    public function copyByIds(array $ids): void
    {
        foreach ($ids as $id) {
            $item = $this->model->find($id);
            $newItem = $item->replicate();
            $newItem->save();
        }
    }

    public function deleteByIds(array $ids): void
    {
        foreach ($ids as $id) {
            $this->delete($id);
        }
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(array $data, $id)
    {
        $model = $this->model->find($id);
        $model->update($data);
        return $model;
    }

    public function getCount()
    {
        return $this->model->count();
    }
}
