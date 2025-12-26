<?php

namespace App\Services;

use App\Models\PurchaseRequest;
use App\Helpers\Helper;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class PurchaseRequestService
{
    protected string $module = 'purchase_requests';

    /**
     * Get all purchase requests with pagination
     */
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = PurchaseRequest::with([
            'product:id,name,sku,thumbnail',
            'warehouse:id,name',
            'supplier:id,name',
            'requester:id,name',
            'approver:id,name'
        ]);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('request_code', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%")
                         ->orWhere('sku', 'like', "%{$search}%");
                  });
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['source'])) {
            $query->where('source', $filters['source']);
        }

        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        if (!empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get purchase request by ID
     */
    public function getById(int $id): PurchaseRequest
    {
        return PurchaseRequest::with([
            'product',
            'warehouse',
            'supplier',
            'requester',
            'approver'
        ])->findOrFail($id);
    }

    /**
     * Create new purchase request
     */
    public function create(array $data): PurchaseRequest
    {
        $data['request_code'] = PurchaseRequest::generateRequestCode();
        $data['status'] = PurchaseRequest::STATUS_PENDING;
        $data['source'] = $data['source'] ?? PurchaseRequest::SOURCE_MANUAL;
        $data['requested_by'] = auth()->id();

        $request = PurchaseRequest::create($data);

        Helper::addLog([
            'action' => 'purchase_request.create',
            'obj_action' => json_encode([$request->id]),
            'new_value' => json_encode($data),
        ]);

        Log::info('Purchase request created', ['request_id' => $request->id]);

        return $request->load(['product', 'warehouse', 'supplier']);
    }

    /**
     * Update purchase request
     */
    public function update(int $id, array $data): PurchaseRequest
    {
        $request = PurchaseRequest::findOrFail($id);
        
        if (!in_array($request->status, [PurchaseRequest::STATUS_PENDING])) {
            throw new \Exception('Chỉ có thể sửa phiếu đang chờ duyệt');
        }

        $oldData = $request->toArray();
        $request->update($data);

        Helper::addLog([
            'action' => 'purchase_request.update',
            'obj_action' => json_encode([$request->id]),
            'old_value' => json_encode($oldData),
            'new_value' => json_encode($data),
        ]);

        return $request->load(['product', 'warehouse', 'supplier']);
    }

    /**
     * Approve purchase request
     */
    public function approve(int $id): PurchaseRequest
    {
        $request = PurchaseRequest::findOrFail($id);
        
        if (!$request->canBeApproved()) {
            throw new \Exception('Không thể duyệt phiếu này');
        }

        $request->update([
            'status' => PurchaseRequest::STATUS_APPROVED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        Helper::addLog([
            'action' => 'purchase_request.approve',
            'obj_action' => json_encode([$request->id]),
        ]);

        Log::info('Purchase request approved', ['request_id' => $request->id]);

        return $request->load(['product', 'warehouse', 'supplier', 'approver']);
    }

    /**
     * Reject purchase request
     */
    public function reject(int $id, ?string $reason = null): PurchaseRequest
    {
        $request = PurchaseRequest::findOrFail($id);
        
        if (!$request->canBeApproved()) {
            throw new \Exception('Không thể từ chối phiếu này');
        }

        $request->update([
            'status' => PurchaseRequest::STATUS_REJECTED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'notes' => $reason ? ($request->notes . "\nLý do từ chối: " . $reason) : $request->notes,
        ]);

        Helper::addLog([
            'action' => 'purchase_request.reject',
            'obj_action' => json_encode([$request->id]),
            'new_value' => json_encode(['reason' => $reason]),
        ]);

        Log::info('Purchase request rejected', ['request_id' => $request->id]);

        return $request->load(['product', 'warehouse', 'supplier', 'approver']);
    }

    /**
     * Mark as ordered
     */
    public function markAsOrdered(int $id): PurchaseRequest
    {
        $request = PurchaseRequest::findOrFail($id);
        
        if ($request->status !== PurchaseRequest::STATUS_APPROVED) {
            throw new \Exception('Chỉ có thể chuyển trạng thái từ đã duyệt sang đã đặt hàng');
        }

        $request->update(['status' => PurchaseRequest::STATUS_ORDERED]);

        Helper::addLog([
            'action' => 'purchase_request.ordered',
            'obj_action' => json_encode([$request->id]),
        ]);

        return $request->load(['product', 'warehouse', 'supplier']);
    }

    /**
     * Mark as completed
     */
    public function complete(int $id): PurchaseRequest
    {
        $request = PurchaseRequest::findOrFail($id);
        
        if ($request->status !== PurchaseRequest::STATUS_ORDERED) {
            throw new \Exception('Chỉ có thể hoàn thành phiếu đã đặt hàng');
        }

        $request->update(['status' => PurchaseRequest::STATUS_COMPLETED]);

        Helper::addLog([
            'action' => 'purchase_request.complete',
            'obj_action' => json_encode([$request->id]),
        ]);

        Log::info('Purchase request completed', ['request_id' => $request->id]);

        return $request->load(['product', 'warehouse', 'supplier']);
    }

    /**
     * Cancel purchase request
     */
    public function cancel(int $id): PurchaseRequest
    {
        $request = PurchaseRequest::findOrFail($id);
        
        if (!$request->canBeCancelled()) {
            throw new \Exception('Không thể hủy phiếu này');
        }

        $oldStatus = $request->status;
        $request->delete(); // Soft delete

        Helper::addLog([
            'action' => 'purchase_request.cancel',
            'obj_action' => json_encode([$request->id]),
            'old_value' => json_encode(['status' => $oldStatus]),
        ]);

        return $request;
    }

    /**
     * Delete purchase request
     */
    public function delete(int $id): bool
    {
        $request = PurchaseRequest::findOrFail($id);
        
        if ($request->status !== PurchaseRequest::STATUS_PENDING) {
            throw new \Exception('Chỉ có thể xóa phiếu đang chờ duyệt');
        }

        Helper::addLog([
            'action' => 'purchase_request.delete',
            'obj_action' => json_encode([$request->id]),
        ]);

        return $request->forceDelete();
    }

    /**
     * Get pending count
     */
    public function getPendingCount(): int
    {
        return PurchaseRequest::pending()->count();
    }

    /**
     * Get summary by status
     */
    public function getSummary(): array
    {
        return PurchaseRequest::query()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }
}
