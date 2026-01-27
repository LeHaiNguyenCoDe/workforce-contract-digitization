<?php

namespace App\Repositories\Contracts;

use App\Models\Quotation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface QuotationRepositoryInterface
{
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function findById(int $id): ?Quotation;
    public function create(array $data): Quotation;
    public function update(Quotation $quotation, array $data): Quotation;
    public function delete(Quotation $quotation): bool;
}
