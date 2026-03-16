<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface RepositoryInterface
{
    public function getOneById(int|string $id): ?Model;

    public function getByIds(array $ids): Collection;

    public function getAll(): Collection;

    public function getQuery(): Builder;

    public function getCount(): int;

    public function create(array $data): Model;

    public function update(array $data, int|string $id): Model;

    public function delete(int|string $id): void;

    public function deleteByIds(array $ids): void;

    public function copyByIds(array $ids): void;
}
