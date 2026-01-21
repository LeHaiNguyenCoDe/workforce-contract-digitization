<?php

namespace App\Repositories;

use App\Models\Quotation;
use App\Repositories\Contracts\QuotationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class QuotationRepository implements QuotationRepositoryInterface
{
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Quotation::with(['customer:id,name,email', 'creator:id,name']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        return $query->latest()->paginate($perPage);
    }

    public function findById(int $id): ?Quotation
    {
        return Quotation::with([
            'customer:id,name,email,phone,address',
            'creator:id,name',
            'items.product:id,name,sku,price'
        ])->find($id);
    }

    public function create(array $data): Quotation
    {
        return Quotation::create($data);
    }

    public function update(Quotation $quotation, array $data): Quotation
    {
        $quotation->update($data);
        return $quotation->fresh();
    }

    public function delete(Quotation $quotation): bool
    {
        return $quotation->delete();
    }
}
