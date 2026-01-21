<?php

namespace App\Services\Admin;

use App\Exceptions\NotFoundException;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Repositories\Contracts\QuotationRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class QuotationService
{
    public function __construct(
        private QuotationRepositoryInterface $quotationRepository
    ) {
    }

    public function getAll(array $filters = [], int $perPage = 15)
    {
        return $this->quotationRepository->getAll($filters, $perPage);
    }

    public function getById(int $id)
    {
        $quotation = $this->quotationRepository->findById($id);
        if (!$quotation) {
            throw new NotFoundException("Quotation with ID {$id} not found");
        }
        return $quotation;
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            // 1. Generate code
            $prefix = 'QT-' . date('Ymd');
            $lastQuotation = Quotation::where('code', 'like', $prefix . '%')
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();
            $sequence = $lastQuotation ? (int) substr($lastQuotation->code, -4) + 1 : 1;
            $code = $prefix . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            // 2. Calculate total
            $totalAmount = 0;
            foreach ($data['items'] as $item) {
                $totalAmount += $item['quantity'] * $item['unit_price'] - ($item['discount'] ?? 0);
            }

            // 3. Create Quotation
            $quotation = $this->quotationRepository->create([
                'code' => $code,
                'name' => $data['name'],
                'customer_id' => $data['customer_id'],
                'user_id' => Auth::id(),
                'status' => 'draft',
                'valid_until' => $data['valid_until'] ?? now()->addDays(30),
                'total_amount' => $totalAmount,
                'note' => $data['note'] ?? null,
            ]);

            // 4. Create Items
            $itemsData = [];
            foreach ($data['items'] as $item) {
                $itemsData[] = [
                    'quotation_id' => $quotation->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'subtotal' => $item['quantity'] * $item['unit_price'] - ($item['discount'] ?? 0),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            QuotationItem::insert($itemsData);

            return $quotation->load(['customer', 'creator', 'items.product']);
        });
    }

    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $quotation = $this->quotationRepository->findById($id);
            if (!$quotation) {
                throw new NotFoundException("Quotation not found");
            }

            if ($quotation->status !== 'draft') {
                throw new \Exception("Chỉ có thể sửa báo giá ở trạng thái nháp");
            }

            $updateData = collect($data)->only(['name', 'customer_id', 'valid_until', 'note'])->toArray();
            $this->quotationRepository->update($quotation, $updateData);

            if (isset($data['items'])) {
                $totalAmount = 0;
                $quotation->items()->delete();

                $itemsData = [];
                foreach ($data['items'] as $item) {
                    $itemTotal = $item['quantity'] * $item['unit_price'] - ($item['discount'] ?? 0);
                    $totalAmount += $itemTotal;

                    $itemsData[] = [
                        'quotation_id' => $quotation->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'discount' => $item['discount'] ?? 0,
                        'subtotal' => $itemTotal,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                QuotationItem::insert($itemsData);

                $this->quotationRepository->update($quotation, ['total_amount' => $totalAmount]);
            }

            return $quotation->fresh(['customer', 'creator', 'items.product']);
        });
    }

    public function delete(int $id)
    {
        $quotation = $this->quotationRepository->findById($id);
        if (!$quotation) {
            throw new NotFoundException("Quotation not found");
        }

        if ($quotation->status !== 'draft') {
            throw new \Exception("Chỉ có thể xóa báo giá ở trạng thái nháp");
        }

        $quotation->items()->delete();
        return $this->quotationRepository->delete($quotation);
    }

    public function send(int $id)
    {
        $quotation = $this->getById($id);
        if ($quotation->status !== 'draft') {
            throw new \Exception("Báo giá đã được gửi");
        }
        return $this->quotationRepository->update($quotation, ['status' => 'sent']);
    }

    public function convertToOrder(int $id)
    {
        return DB::transaction(function () use ($id) {
            $quotation = Quotation::with('items')->findOrFail($id);

            if (!in_array($quotation->status, ['sent', 'accepted'])) {
                throw new \Exception("Báo giá chưa được gửi hoặc chấp nhận");
            }

            $order = \App\Models\Order::create([
                'order_number' => 'ORD-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                'user_id' => $quotation->customer_id,
                'total' => $quotation->total_amount,
                'status' => 'pending',
                'notes' => 'Chuyển từ báo giá: ' . $quotation->code,
            ]);

            foreach ($quotation->items as $item) {
                \App\Models\OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->unit_price,
                ]);
            }

            $this->quotationRepository->update($quotation, ['status' => 'converted']);

            return [
                'quotation' => $quotation->fresh(),
                'order' => $order,
            ];
        });
    }
}
