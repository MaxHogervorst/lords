<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface RepositoryInterface
{
    /**
     * Get all records.
     */
    public function all(array $columns = ['*'], array $relations = []): Collection;

    /**
     * Find a record by ID.
     */
    public function find(int $id, array $columns = ['*'], array $relations = []): ?Model;

    /**
     * Find a record by ID or fail.
     */
    public function findOrFail(int $id, array $columns = ['*'], array $relations = []): Model;

    /**
     * Create a new record.
     */
    public function create(array $data): Model;

    /**
     * Update a record.
     */
    public function update(Model $model, array $data): Model;

    /**
     * Delete a record.
     */
    public function delete(Model $model): bool;

    /**
     * Find records by a specific column value.
     */
    public function findBy(string $column, mixed $value, array $columns = ['*']): Collection;

    /**
     * Find first record by a specific column value.
     */
    public function findOneBy(string $column, mixed $value, array $columns = ['*']): ?Model;
}
