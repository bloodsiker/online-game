<?php

namespace App\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

interface RepositoryInterface {

  public function getModelClass(): string;

  public function getOneById($id): ?Model;

  public function getByIds(array $ids): Collection;

  public function getAll(): Collection;

  public function getQuery();

  public function delete($id);

  public function copyByIds(array $ids);

  public function deleteByIds(array $ids);

  public function create(array $data);

  public function update(array $data, $id);
}
