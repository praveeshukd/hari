<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use LogicException;
use RuntimeException;

abstract class EloquentRepository implements RepositoryInterface
{
    /**
     * @return class-string<Model>
     */
    abstract protected function modelClass(): string;

    public function find(int $id): ?Model
    {
        return $this->newQuery()->find($id);
    }

    public function findOrFail(int $id): Model
    {
        return $this->newQuery()->findOrFail($id);
    }

    public function findWithTrashed(int $id): ?Model
    {
        return $this->newQuery(withTrashed: true)->find($id);
    }

    public function findManyByIds(array $ids, bool $withTrashed = false): Collection
    {
        $ids = array_values(array_filter($ids, static fn ($id) => $id !== null && $id !== ''));

        if ($ids === []) {
            return collect();
        }

        return $this->newQuery(withTrashed: $withTrashed)
            ->whereIn($this->newModel()->getKeyName(), $ids)
            ->get();
    }

    public function create(array $attributes): Model
    {
        return $this->newQuery()->create($attributes);
    }

    public function update(Model $model, array $attributes): Model
    {
        $this->assertModelType($model);
        $model->update($attributes);
        $model->refresh();

        return $model;
    }

    public function updateField(Model $model, string $field, mixed $value, array $extra = []): Model
    {
        $this->assertModelType($model);
        $model->{$field} = $value;
        foreach ($extra as $key => $extraValue) {
            $model->{$key} = $extraValue;
        }
        $model->save();
        $model->refresh();

        return $model;
    }

    public function delete(Model $model): void
    {
        $this->assertModelType($model);
        $model->delete();
    }

    public function restore(Model $model): void
    {
        $this->assertModelType($model);

        if (! $this->usesSoftDeletes()) {
            throw new LogicException(sprintf(
                'Model [%s] does not use SoftDeletes; restore() is unavailable.',
                $this->modelClass()
            ));
        }

        if (! method_exists($model, 'restore')) {
            throw new RuntimeException('SoftDeletes restore() is not available on model instance.');
        }

        $model->restore();
    }

    protected function newModel(): Model
    {
        $class = $this->modelClass();

        return new $class;
    }

    protected function newQuery(bool $withTrashed = false, bool $onlyTrashed = false): Builder
    {
        $query = $this->modelClass()::query();

        if ($onlyTrashed) {
            if (! $this->usesSoftDeletes()) {
                throw new LogicException(sprintf(
                    'Model [%s] does not use SoftDeletes; onlyTrashed() is unavailable.',
                    $this->modelClass()
                ));
            }

            return $this->modelClass()::onlyTrashed();
        }

        if ($withTrashed && $this->usesSoftDeletes()) {
            return $this->modelClass()::withTrashed();
        }

        return $query;
    }

    protected function usesSoftDeletes(): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive($this->modelClass()), true);
    }

    protected function assertModelType(Model $model): void
    {
        $expected = $this->modelClass();
        if (! $model instanceof $expected) {
            throw new LogicException(sprintf(
                'Expected model of type [%s], got [%s].',
                $expected,
                $model::class
            ));
        }
    }
}
