<?php

namespace App\Services\Admin;

use App\Exceptions\BusinessLogicException;
use App\Exceptions\NotFoundException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\LoyaltyAccount;
use App\Models\LoyaltyTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Promotion;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\PromotionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private PromotionRepositoryInterface $promotionRepository,
        private \App\Repositories\Contracts\ShipmentRepositoryInterface $shipmentRepository
    ) {
    }

    /**
     * Get all orders (for admin)
     */
    public function getAll(int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        return $this->orderRepository->getAll($perPage, $filters);
    }

    /**
     * Get orders by user
     */
    public function getByUserId(?int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->orderRepository->getByUserId($userId, $perPage);
    }

    /**
     * Get order details
     */
    public function getById(int $id): Order
    {
        $order = $this->orderRepository->findById($id);

        if (!$order) {
            throw new NotFoundException("Order with ID {$id} not found");
        }

        $order->load(['items.product', 'user']);

        return $order;
    }

    /**
     * Update order status
     */
    public function updateStatus(int $id, string $status): Order
    {
        $order = $this->getById($id);
        return $this->orderRepository->update($order, ['status' => $status]);
    }

    /**
     * Create order from cart
     */
    public function createFromCart(array $data, ?Cart $cart, ?int $userId): Order
    {
        if (!$cart || $cart->items->isEmpty()) {
            throw new BusinessLogicException('Cart is empty');
        }

        $order = null;

        DB::transaction(function () use (&$order, $cart, $data, $userId) {
            $total = $this->calculateCartTotal($cart);

            $order = $this->orderRepository->create([
                'user_id' => $userId,
                'code' => Str::upper(Str::random(8)),
                'full_name' => $data['full_name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'address_line' => $data['address_line'],
                'ward' => $data['ward'] ?? null,
                'district' => $data['district'] ?? null,
                'province' => $data['province'] ?? null,
                'payment_method' => $data['payment_method'],
                'status' => Order::STATUS_PENDING,
                'total_amount' => 0,
                'note' => $data['note'] ?? null,
            ]);

            $this->createOrderItems($order, $cart);

            $discount = $this->applyPromotion($order, $total, $data['promotion_code'] ?? null);
            $totalAfterPromotion = max($total - $discount, 0);

            $loyaltyUsed = $this->applyLoyaltyPoints(
                $order,
                $totalAfterPromotion,
                $userId,
                $data['use_points'] ?? 0
            );

            $finalTotal = max($totalAfterPromotion - $loyaltyUsed, 0);
            $this->earnLoyaltyPoints($order, $finalTotal, $userId);

            $this->orderRepository->update($order, ['total_amount' => $finalTotal]);

            $cart->items()->delete();
        });

        return $this->orderRepository->findById($order->id)->load('items.product');
    }

    /**
     * Calculate cart total
     */
    private function calculateCartTotal(Cart $cart): int
    {
        $total = 0;
        foreach ($cart->items as $item) {
            $total += $item->qty * $item->price;
        }
        return $total;
    }

    /**
     * Create order items from cart items
     */
    private function createOrderItems(Order $order, Cart $cart): void
    {
        foreach ($cart->items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'qty' => $item->qty,
                'price' => $item->price,
                'subtotal' => $item->qty * $item->price,
            ]);
        }
    }

    /**
     * Apply promotion code
     */
    private function applyPromotion(Order $order, int $total, ?string $promotionCode): int
    {
        if (!$promotionCode) {
            return 0;
        }

        $promotion = $this->promotionRepository->findByCode($promotionCode);

        // Check if promotion is active and valid
        if ($promotion && $promotion->is_active) {
            $now = now();
            $isValid = (!$promotion->starts_at || $promotion->starts_at <= $now) &&
                (!$promotion->ends_at || $promotion->ends_at >= $now);

            if (!$isValid) {
                $promotion = null;
            }
        } else {
            $promotion = null;
        }

        if (!$promotion) {
            return 0;
        }

        if ($promotion->type === 'percent') {
            return intdiv($total * $promotion->value, 100);
        }

        return min($total, $promotion->value);
    }

    /**
     * Apply loyalty points
     */
    private function applyLoyaltyPoints(Order $order, int $totalAfterPromotion, ?int $userId, int $usePoints): int
    {
        if (!$userId || $usePoints <= 0) {
            return 0;
        }

        $account = LoyaltyAccount::firstOrCreate(
            ['user_id' => $userId],
            ['points' => 0]
        );

        $usablePoints = min($usePoints, $account->points);
        $loyaltyDiscount = min($totalAfterPromotion, $usablePoints);

        if ($loyaltyDiscount > 0) {
            $account->decrement('points', $loyaltyDiscount);

            LoyaltyTransaction::create([
                'loyalty_account_id' => $account->id,
                'points' => -$loyaltyDiscount,
                'type' => 'redeem',
                'reference_type' => 'order',
                'reference_id' => $order->id,
                'note' => 'Redeem points for order ' . $order->code,
            ]);
        }

        return $loyaltyDiscount;
    }

    /**
     * Earn loyalty points from order
     */
    private function earnLoyaltyPoints(Order $order, int $total, ?int $userId): void
    {
        if (!$userId || $total <= 0) {
            return;
        }

        $account = LoyaltyAccount::firstOrCreate(
            ['user_id' => $userId],
            ['points' => 0]
        );

        $earned = intdiv($total, 100); // 1 point per 100 units

        if ($earned > 0) {
            $account->increment('points', $earned);

            LoyaltyTransaction::create([
                'loyalty_account_id' => $account->id,
                'points' => $earned,
                'type' => 'earn',
                'reference_type' => 'order',
                'reference_id' => $order->id,
                'note' => 'Earn points from order ' . $order->code,
            ]);
        }
    }

    /**
     * Resolve cart from request
     */
    public function resolveCart(Request $request): ?Cart
    {
        $user = Auth::user();

        if ($user) {
            return Cart::with('items.product')->where('user_id', $user->id)->first();
        }

        $sessionId = $request->session()->getId();
        return Cart::with('items.product')->where('session_id', $sessionId)->first();
    }

    /**
     * Check if stock is available for all items in an order
     */
    public function checkStockAvailability(Order $order): array
    {
        $results = [];
        $isAvailable = true;

        foreach ($order->items as $item) {
            $stock = \App\Models\Stock::where('product_id', $item->product_id)
                ->where('product_variant_id', $item->product_variant_id)
                ->sum('quantity');

            $results[] = [
                'product_id' => $item->product_id,
                'name' => $item->product->name,
                'requested' => $item->qty,
                'available' => (int) $stock,
                'is_sufficient' => $stock >= $item->qty
            ];

            if ($stock < $item->qty) {
                $isAvailable = false;
            }
        }

        return [
            'is_available' => $isAvailable,
            'items' => $results
        ];
    }

    /**
     * Reduce stock levels after order approval
     */
    public function updateStockLevels(Order $order): void
    {
        foreach ($order->items as $item) {
            $stock = \App\Models\Stock::where('product_id', $item->product_id)
                ->where('product_variant_id', $item->product_variant_id)
                ->first();

            if ($stock) {
                $quantityBefore = $stock->quantity;
                $stock->decrement('quantity', $item->qty);

                // Record movement using InventoryLog (BR-09.2)
                \App\Models\InventoryLog::create([
                    'warehouse_id' => $stock->warehouse_id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'movement_type' => \App\Models\InventoryLog::MOVEMENT_TYPE_OUTBOUND,
                    'quantity' => -$item->qty,
                    'quantity_before' => $quantityBefore,
                    'quantity_after' => $quantityBefore - $item->qty,
                    'reference_type' => 'order',
                    'reference_id' => $order->id,
                    'note' => 'Order approval for ' . $order->code,
                ]);
            }
        }
    }

    /**
     * Assign a shipper to an order
     */
    public function assignShipper(Order $order, int $shipperId): \App\Models\Shipment
    {
        $shipment = $this->shipmentRepository->updateOrCreate(
            ['order_id' => $order->id],
            [
                'shipper_id' => $shipperId,
                'status' => 'pending',
                'tracking_number' => 'TRK-' . strtoupper(Str::random(10)),
            ]
        );

        $this->orderRepository->update($order, ['status' => 'processing']);

        return $shipment;
    }

    /**
     * Update shipment tracking
     */
    public function updateTracking(int $orderId, array $data): \App\Models\Shipment
    {
        $shipment = $this->shipmentRepository->findByOrderId($orderId);
        
        if (!$shipment) {
            throw new NotFoundException('Shipment not found');
        }

        $updateData = [
            'current_lat' => $data['lat'],
            'current_lng' => $data['lng'],
        ];

        if (isset($data['status'])) {
            $updateData['status'] = $data['status'];
        }

        return $this->shipmentRepository->update($shipment, $updateData);
    }

    /**
     * Confirm order (BR-SALES-02 + BR-INVENTORY-01)
     */
    public function confirmOrder(Order $order): Order
    {
        if (!$order->canConfirm()) {
            throw new BusinessLogicException('Đơn hàng không thể xác nhận!');
        }

        $availability = $this->checkStockAvailability($order);
        if (!$availability['is_available']) {
            throw new BusinessLogicException('Tồn kho không đủ!');
        }

        return DB::transaction(function () use ($order) {
            $this->updateStockLevels($order);
            $costAmount = $this->calculateOrderCost($order);

            return $this->orderRepository->update($order, [
                'status' => Order::STATUS_CONFIRMED,
                'confirmed_at' => now(),
                'cost_amount' => $costAmount,
                'remaining_amount' => $order->total_amount - $order->paid_amount,
            ]);
        });
    }

    /**
     * Mark order as delivered (BR-SALES-02)
     */
    public function markDelivered(Order $order): Order
    {
        return $this->orderRepository->update($order, [
            'status' => Order::STATUS_DELIVERED,
            'delivered_at' => now(),
        ]);
    }

    /**
     * Complete order (BR-SALES-03, BR-DEBT-01)
     */
    public function completeOrder(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            $order = $this->orderRepository->update($order, [
                'status' => Order::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);

            if (!$order->isFullyPaid()) {
                $debtService = app(DebtService::class);
                $debtService->createReceivableFromOrder($order);
            }

            return $order;
        });
    }

    /**
     * Cancel order (BR-SALES-05)
     */
    public function cancelOrder(Order $order, ?string $reason = null): Order
    {
        if ($order->status === Order::STATUS_CANCELLED) {
             throw new BusinessLogicException('Đơn hàng đã bị hủy trước đó!');
        }

        if (in_array($order->status, [Order::STATUS_COMPLETED, Order::STATUS_DELIVERED, Order::STATUS_SHIPPED])) {
            throw new BusinessLogicException('Không thể hủy đơn hàng ở trạng thái này!');
        }

        return DB::transaction(function () use ($order, $reason) {
            if (in_array($order->status, [Order::STATUS_CONFIRMED, Order::STATUS_PROCESSING])) {
                $this->returnStockLevels($order);
            }

            return $this->orderRepository->update($order, [
                'status' => Order::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'note' => $order->note . ($reason ? " (Hủy: $reason)" : ""),
            ]);
        });
    }

    /**
     * Return stock levels after order cancellation
     */
    private function returnStockLevels(Order $order): void
    {
        foreach ($order->items as $item) {
            $stock = \App\Models\Stock::where('product_id', $item->product_id)
                ->where('product_variant_id', $item->product_variant_id)
                ->first();

            if ($stock) {
                $quantityBefore = $stock->quantity;
                $stock->increment('quantity', $item->qty);

                // Record return movement using InventoryLog (BR-09.2)
                \App\Models\InventoryLog::create([
                    'warehouse_id' => $stock->warehouse_id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'movement_type' => \App\Models\InventoryLog::MOVEMENT_TYPE_RETURN,
                    'quantity' => $item->qty,
                    'quantity_before' => $quantityBefore,
                    'quantity_after' => $quantityBefore + $item->qty,
                    'reference_type' => 'order_cancel',
                    'reference_id' => $order->id,
                    'note' => 'Order cancelled: ' . $order->code,
                ]);
            }
        }
    }

    /**
     * Calculate total cost of an order
     */
    private function calculateOrderCost(Order $order): float
    {
        $totalCost = 0;
        foreach ($order->items as $item) {
            $cost = $item->product->cost_price ?? 0;
            $totalCost += $cost * $item->qty;
        }
        return $totalCost;
    }
}
