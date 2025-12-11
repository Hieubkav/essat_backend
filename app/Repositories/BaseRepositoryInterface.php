<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;

/**
 * Base Repository Interface
 * 
 * Defines common methods for all repository implementations
 */
interface BaseRepositoryInterface
{
    /**
     * Get all records
     */
    public function all();

    /**
     * Get paginated records
     */
    public function paginate(int $perPage = 15);

    /**
     * Find record by ID
     */
    public function find(int|string $id): ?Model;

    /**
     * Find record or fail
     */
    public function findOrFail(int|string $id): Model;

    /**
     * Create a new record
     */
    public function create(array $data): Model;

    /**
     * Update a record
     */
    public function update(int|string $id, array $data): bool;

    /**
     * Delete a record
     */
    public function delete(int|string $id): bool;

    /**
     * Get count of records
     */
    public function count(): int;
}
