<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements RepositoryInterface
{
    protected Model $model;

    public function __construct()
    {
        $this->model = $this->makeModel();
    }

    /**
     * Create model instance.
     */
    abstract protected function makeModel(): Model;

    /**
     * Get all records.
     */
    public function all(array $columns = ['*'], array $relations = []): Collection
    {
        $query = $this->model->select($columns);

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->get();
    }

    /**
     * Find a record by ID.
     */
    public function find(int $id, array $columns = ['*'], array $relations = []): ?Model
    {
        $query = $this->model->select($columns);

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->find($id);
    }

    /**
     * Find a record by ID or fail.
     */
    public function findOrFail(int $id, array $columns = ['*'], array $relations = []): Model
    {
        $query = $this->model->select($columns);

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->findOrFail($id);
    }

    /**
     * Create a new record.
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Update a record.
     */
    public function update(Model $model, array $data): Model
    {
        $model->update($data);

        return $model;
    }

    /**
     * Delete a record.
     */
    public function delete(Model $model): bool
    {
        return $model->delete();
    }

    /**
     * Find records by a specific column value.
     */
    public function findBy(string $column, mixed $value, array $relations = [], array $columns = ['*']): Collection
    {
        $query = $this->model->newQuery()->select($columns)->where($column, $value);

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->get();
    }

    /**
     * Find first record by a specific column value.
     */
    public function findOneBy(string $column, mixed $value, array $relations = [], array $columns = ['*']): ?Model
    {
        $query = $this->model->newQuery()->select($columns)->where($column, $value);

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->first();
    }

    /**
     * Get query builder instance.
     */
    protected function query()
    {
        return $this->model->newQuery();
    }
}
