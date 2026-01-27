<?php

namespace App\Repositories\Contracts;

use App\Models\Batch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface BatchRepositoryInterface
{
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function findById(int $id): ?Batch;
    public function create(array $data): Batch;
    public function update(Batch $batch, array $data): Batch;
    public function delete(int $id): bool;
    public function getAvailable(int $productId, ?int $warehouseId = null): array;
    public function deductQuantity(Batch $batch, int $quantity): Batch;
}
