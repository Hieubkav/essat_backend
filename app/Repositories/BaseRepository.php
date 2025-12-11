<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Base Repository
 * 
 * Provides common database operations for all repository implementations
 */
abstract class BaseRepository implements BaseRepositoryInterface
{
    /**
     * The model instance
     */
    protected Model $model;

    /**
     * Create a new repository instance.
     */
    abstract public function __construct();

    /**
     * Get all records
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Get paginated records
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }

    /**
     * Find record by ID
     */
    public function find(int|string $id): ?Model
    {
        return $this->model->find($id);
    }

    /**
     * Find record or fail
     */
    public function findOrFail(int|string $id): Model
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create a new record
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Update a record
     */
    public function update(int|string $id, array $data): bool
    {
        $record = $this->findOrFail($id);
        return $record->update($data);
    }

    /**
     * Delete a record
     */
    public function delete(int|string $id): bool
    {
        $record = $this->findOrFail($id);
        return $record->delete();
    }

    /**
     * Get count of records
     */
    public function count(): int
    {
        return $this->model->count();
    }
}
