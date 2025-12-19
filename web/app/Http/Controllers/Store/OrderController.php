<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\LoyaltyAccount;
use App\Models\LoyaltyTransaction;
use App\Models\Promotion;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    protected function resolveCart(Request $request): ?Cart
    {
        $user = Auth::user();

        if ($user) {
            return Cart::with('items.product')->where('user_id', $user->id)->first();
        }

        $sessionId = $request->session()->getId();

        return Cart::with('items.product')->where('session_id', $sessionId)->first();
    }

    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        $orders = Order::query()
            ->where('user_id', $user?->id)
            ->latest()
            ->paginate($request->query('per_page', 10));

        return response()->json($orders);
    }

    public function show(Order $order): JsonResponse
    {
        $order->load('items.product');

        return response()->json([
            'data' => $order,
        ]);
    }

    /**
     * Tạo đơn hàng từ giỏ hàng hiện tại
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email'],
            'address_line' => ['required', 'string', 'max:255'],
            'ward' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', 'string'], // cod, credit_card, bank_transfer, e_wallet
            'note' => ['nullable', 'string'],
            'promotion_code' => ['nullable', 'string', 'max:50'],
            'use_points' => ['nullable', 'integer', 'min:0'],
        ]);

        $cart = $this->resolveCart($request);

        if (! $cart || $cart->items->isEmpty()) {
            return response()->json([
                'message' => 'Cart is empty',
            ], 400);
        }

        $user = Auth::user();

        $order = null;

        DB::transaction(function () use (&$order, $cart, $validated, $user) {
            $total = 0;

            $order = Order::create([
                'user_id' => $user?->id,
                'code' => Str::upper(Str::random(8)),
                'full_name' => $validated['full_name'],
                'phone' => $validated['phone'],
                'email' => $validated['email'] ?? $user?->email,
                'address_line' => $validated['address_line'],
                'ward' => $validated['ward'] ?? null,
                'district' => $validated['district'] ?? null,
                'province' => $validated['province'] ?? null,
                'payment_method' => $validated['payment_method'],
                'status' => Order::STATUS_PENDING,
                'total_amount' => 0,
                'note' => $validated['note'] ?? null,
            ]);

            /** @var CartItem $item */
            foreach ($cart->items as $item) {
                $subtotal = $item->qty * $item->price;
                $total += $subtotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'qty' => $item->qty,
                    'price' => $item->price,
                    'subtotal' => $subtotal,
                ]);
            }

            // Áp dụng promotion theo code (nếu có)
            $discount = 0;
            if (!empty($validated['promotion_code'])) {
                $promotion = Promotion::query()
                    ->where('code', $validated['promotion_code'])
                    ->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
                    })
                    ->first();

                if ($promotion) {
                    if ($promotion->type === 'percent') {
                        $discount = intdiv($total * $promotion->value, 100);
                    } else { // fixed_amount
                        $discount = min($total, $promotion->value);
                    }
                }
            }

            $totalAfterPromotion = max($total - $discount, 0);

            // Sử dụng điểm loyalty (nếu user đăng nhập và có yêu cầu)
            $loyaltyUsed = 0;
            if ($user && !empty($validated['use_points']) && $validated['use_points'] > 0) {
                $account = LoyaltyAccount::firstOrCreate(
                    ['user_id' => $user->id],
                    ['points' => 0]
                );

                $usablePoints = min($validated['use_points'], $account->points);
                $loyaltyDiscount = min($totalAfterPromotion, $usablePoints);

                if ($loyaltyDiscount > 0) {
                    $loyaltyUsed = $loyaltyDiscount;
                    $totalAfterPromotion -= $loyaltyDiscount;

                    $account->decrement('points', $loyaltyDiscount);

                    LoyaltyTransaction::create([
                        'loyalty_account_id' => $account->id,
                        'points' => -$loyaltyDiscount,
                        'type' => 'redeem',
                        'reference_type' => 'order',
                        'reference_id' => $order->id,
                        'note' => 'Redeem points for order '.$order->code,
                    ]);
                }
            }

            // Cộng điểm: 1% giá trị đơn sau giảm giá
            if ($user && $totalAfterPromotion > 0) {
                $account = LoyaltyAccount::firstOrCreate(
                    ['user_id' => $user->id],
                    ['points' => 0]
                );

                $earned = intdiv($totalAfterPromotion, 100); // 1 point cho mỗi 100 đơn vị tiền

                if ($earned > 0) {
                    $account->increment('points', $earned);

                    LoyaltyTransaction::create([
                        'loyalty_account_id' => $account->id,
                        'points' => $earned,
                        'type' => 'earn',
                        'reference_type' => 'order',
                        'reference_id' => $order->id,
                        'note' => 'Earn points from order '.$order->code,
                    ]);
                }
            }

            $order->update(['total_amount' => $totalAfterPromotion]);

            // Xoá giỏ hàng sau khi tạo đơn
            $cart->items()->delete();
        });

        $order->load('items.product');

        return response()->json([
            'message' => 'Order created successfully',
            'data' => $order,
        ], 201);
    }
}


