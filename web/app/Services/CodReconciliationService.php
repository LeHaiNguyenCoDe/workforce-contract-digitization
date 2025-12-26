<?php

namespace App\Services;

use App\Models\CodReconciliation;
use App\Models\CodReconciliationItem;
use App\Models\Order;
use App\Helpers\Helper;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CodReconciliationService
{
    /**
     * Get all reconciliations
     */
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = CodReconciliation::query()
            ->with('creator:id,name')
            ->withCount('items');

        if (!empty($filters['shipping_partner'])) {
            $query->where('shipping_partner', $filters['shipping_partner']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get by ID
     */
    public function getById(int $id): CodReconciliation
    {
        return CodReconciliation::with(['items.order', 'creator'])->findOrFail($id);
    }

    /**
     * Create reconciliation from orders
     */
    public function create(array $data): CodReconciliation
    {
        $reconciliation = CodReconciliation::create([
            'reconciliation_code' => CodReconciliation::generateCode(),
            'shipping_partner' => $data['shipping_partner'],
            'period_from' => $data['period_from'],
            'period_to' => $data['period_to'],
            'status' => CodReconciliation::STATUS_DRAFT,
            'notes' => $data['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        // Auto-populate with COD orders in period
        $this->populateFromOrders($reconciliation);

        Helper::addLog([
            'action' => 'cod_reconciliation.create',
            'obj_action' => json_encode([$reconciliation->id]),
        ]);

        return $reconciliation->load('items.order');
    }

    /**
     * Populate items from COD orders
     */
    protected function populateFromOrders(CodReconciliation $reconciliation): void
    {
        $orders = Order::query()
            ->where('payment_method', 'cod')
            ->whereIn('status', ['delivered', 'completed'])
            ->whereBetween('created_at', [
                $reconciliation->period_from,
                $reconciliation->period_to->endOfDay()
            ])
            ->get();

        foreach ($orders as $order) {
            CodReconciliationItem::create([
                'reconciliation_id' => $reconciliation->id,
                'order_id' => $order->id,
                'tracking_number' => $order->tracking_number,
                'expected_amount' => $order->total,
                'received_amount' => null,
                'status' => CodReconciliationItem::STATUS_PENDING,
            ]);
        }

        $reconciliation->calculateTotals();
    }

    /**
     * Update item received amounts
     */
    public function updateItems(int $id, array $items): CodReconciliation
    {
        $reconciliation = CodReconciliation::findOrFail($id);

        foreach ($items as $itemData) {
            $item = CodReconciliationItem::find($itemData['id']);
            if ($item && $item->reconciliation_id === $reconciliation->id) {
                $item->setReceived((float)$itemData['received_amount']);
                if (!empty($itemData['notes'])) {
                    $item->notes = $itemData['notes'];
                    $item->save();
                }
            }
        }

        $reconciliation->calculateTotals();

        return $reconciliation->load('items.order');
    }

    /**
     * Mark as reconciled
     */
    public function reconcile(int $id): CodReconciliation
    {
        $reconciliation = CodReconciliation::findOrFail($id);

        $reconciliation->reconciled_at = now();
        $reconciliation->reconciled_by = auth()->id();

        if ($reconciliation->difference == 0) {
            $reconciliation->status = CodReconciliation::STATUS_MATCHED;
        } else {
            $reconciliation->status = CodReconciliation::STATUS_RESOLVED;
        }

        $reconciliation->save();

        Helper::addLog([
            'action' => 'cod_reconciliation.reconcile',
            'obj_action' => json_encode([$reconciliation->id]),
        ]);

        return $reconciliation;
    }

    /**
     * Delete (only draft)
     */
    public function delete(int $id): bool
    {
        $reconciliation = CodReconciliation::findOrFail($id);

        if ($reconciliation->status !== CodReconciliation::STATUS_DRAFT) {
            throw new \Exception('Chỉ có thể xóa bản nháp');
        }

        return $reconciliation->delete();
    }

    /**
     * Get shipping partners list
     */
    public function getShippingPartners(): array
    {
        return [
            ['code' => 'ghn', 'name' => 'Giao Hàng Nhanh'],
            ['code' => 'ghtk', 'name' => 'Giao Hàng Tiết Kiệm'],
            ['code' => 'viettelpost', 'name' => 'Viettel Post'],
            ['code' => 'jt', 'name' => 'J&T Express'],
            ['code' => 'ninja', 'name' => 'Ninja Van'],
            ['code' => 'other', 'name' => 'Khác'],
        ];
    }
}
