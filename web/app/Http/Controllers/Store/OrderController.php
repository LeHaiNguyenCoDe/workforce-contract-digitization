<?php

namespace App\Http\Controllers\Store;

use App\Exceptions\BusinessLogicException;
use App\Exceptions\NotFoundException;
use App\Helpers\LanguageHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\OrderRequest;
use App\Models\Order;
use App\Services\OrderService;
use App\Traits\TranslatableResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    use TranslatableResponse;

    public function __construct(
        private OrderService $orderService
    ) {
    }

    /**
     * Get user orders
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            $perPage = $request->query('per_page', 10);

            $orders = $this->orderService->getByUserId($userId, $perPage);

            return $this->successResponse('success', $orders);
        } catch (\Exception $ex) {
            return $this->genericErrorResponse(500, $ex);
        }
    }

    /**
     * Get order details
     */
    public function show(Order $order): JsonResponse
    {
        try {
            $orderData = $this->orderService->getById($order->id);

            return response()->json([
                'status' => 'success',
                'data' => $orderData,
            ]);
        } catch (NotFoundException $ex) {
            return $this->errorResponse('order_not_found', null, 404);
        } catch (\Exception $ex) {
            return $this->genericErrorResponse(500, $ex);
        }
    }

    /**
     * Create order from cart
     */
    public function store(OrderRequest $request): JsonResponse
    {
        try {
            $cart = $this->orderService->resolveCart($request);
            $userId = Auth::id();

            $order = $this->orderService->createFromCart($request->validated(), $cart, $userId);

            return $this->successResponse('order_created', $order, 201);
        } catch (BusinessLogicException $ex) {
            return $this->errorResponse('cart_empty', null, 400);
        } catch (\Exception $ex) {
            return $this->genericErrorResponse(500, $ex);
        }
    }

    /**
     * Update order status
     */
    public function updateStatus(Order $order, Request $request): JsonResponse
    {
        try {
            if (!$order || !$order->id) {
                return $this->errorResponse('order_not_found', null, 404);
            }

            $request->validate([
                'status' => 'required|string|in:pending,processing,shipped,delivered,cancelled',
            ]);

            $order->update(['status' => $request->status]);

            return response()->json([
                'status' => 'success',
                'message' => 'Order status updated',
                'data' => $order->fresh(),
            ]);
        } catch (NotFoundException $ex) {
            return $this->errorResponse('order_not_found', null, 404);
        } catch (\Exception $ex) {
            return $this->genericErrorResponse(500, $ex);
        }
    }

    /**
     * Cancel order
     */
    public function cancel(Order $order): JsonResponse
    {
        try {
            if (!$order || !$order->id) {
                return $this->errorResponse('order_not_found', null, 404);
            }

            if ($order->status === 'cancelled') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order is already cancelled',
                ], 400);
            }

            if (in_array($order->status, ['delivered', 'shipped'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot cancel order that is already shipped or delivered',
                ], 400);
            }

            $order->update(['status' => 'cancelled']);

            return response()->json([
                'status' => 'success',
                'message' => 'Order cancelled',
                'data' => $order->fresh(),
            ]);
        } catch (NotFoundException $ex) {
            return $this->errorResponse('order_not_found', null, 404);
        } catch (\Exception $ex) {
            return $this->genericErrorResponse(500, $ex);
        }
    }
}


