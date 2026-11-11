<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface RepositoryInterface
{
    public function find(int $id): ?Model;

    public function findOrFail(int $id): Model;

    public function findWithTrashed(int $id): ?Model;

    /**
     * @param  list<int|string>  $ids
     * @return Collection<int, Model>
     */
    public function findManyByIds(array $ids, bool $withTrashed = false): Collection;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Model;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Model $model, array $attributes): Model;

    /**
     * @param  array<string, mixed>  $extra
     */
    public function updateField(Model $model, string $field, mixed $value, array $extra = []): Model;

    public function delete(Model $model): void;

    public function restore(Model $model): void;
}
