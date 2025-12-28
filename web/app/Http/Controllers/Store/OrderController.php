<?php

namespace App\Http\Controllers\Store;

use App\Exceptions\BusinessLogicException;
use App\Exceptions\NotFoundException;
use App\Helpers\LanguageHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\OrderRequest;
use App\Http\Requests\Store\OrderStatusRequest;
use App\Http\Requests\Store\AssignShipperRequest;
use App\Http\Requests\Store\UpdateTrackingRequest;
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

            // If admin, include stock check results
            if (Auth::user() && Auth::user()->role !== 'customer') {
                $orderData->stock_check = $this->orderService->checkStockAvailability($orderData);
            }

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
    public function updateStatus(Order $order, OrderStatusRequest $request): JsonResponse
    {
        try {
            if (!$order || !$order->id) {
                return $this->errorResponse('order_not_found', null, 404);
            }

            $order->update(['status' => $request->validated()['status']]);

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

    /**
     * Check stock for an order
     */
    public function checkStock(Order $order): JsonResponse
    {
        try {
            $orderData = $this->orderService->getById($order->id);
            $stockInfo = $this->orderService->checkStockAvailability($orderData);

            return response()->json([
                'status' => 'success',
                'data' => $stockInfo,
            ]);
        } catch (\Exception $ex) {
            return $this->genericErrorResponse(500, $ex);
        }
    }

    /**
     * Assign shipper to order
     */
    public function assignShipper(Order $order, AssignShipperRequest $request): JsonResponse
    {
        try {
            $shipment = $this->orderService->assignShipper($order, $request->validated()['shipper_id']);

            return response()->json([
                'status' => 'success',
                'message' => 'Shipper assigned and order status updated to processing',
                'data' => $shipment,
            ]);
        } catch (\Exception $ex) {
            return $this->genericErrorResponse(500, $ex);
        }
    }

    /**
     * Update shipment tracking/GPS
     */
    public function updateTracking(Order $order, UpdateTrackingRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $shipment = \App\Models\Shipment::where('order_id', $order->id)->firstOrFail();

            $shipment->update([
                'current_lat' => $validated['lat'],
                'current_lng' => $validated['lng'],
            ]);

            if (isset($validated['status'])) {
                $shipment->update(['status' => $validated['status']]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Tracking updated',
                'data' => $shipment,
            ]);
        } catch (\Exception $ex) {
            return $this->genericErrorResponse(500, $ex);
        }
    }

    /**
     * Confirm order (BR-SALES-02)
     */
    public function confirmOrder(Order $order): JsonResponse
    {
        try {
            $updatedOrder = $this->orderService->confirmOrder($order);
            return response()->json([
                'status' => 'success',
                'message' => 'Đơn hàng đã được xác nhận và xuất kho',
                'data' => $updatedOrder,
            ]);
        } catch (BusinessLogicException $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], 400);
        } catch (\Exception $ex) {
            return $this->genericErrorResponse(500, $ex);
        }
    }

    /**
     * Mark order as delivered (BR-SALES-02)
     */
    public function markDelivered(Order $order): JsonResponse
    {
        try {
            $updatedOrder = $this->orderService->markDelivered($order);
            return response()->json([
                'status' => 'success',
                'message' => 'Đơn hàng đã được đánh dấu là đã giao',
                'data' => $updatedOrder,
            ]);
        } catch (BusinessLogicException $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], 400);
        } catch (\Exception $ex) {
            return $this->genericErrorResponse(500, $ex);
        }
    }

    /**
     * Complete order (BR-SALES-03)
     */
    public function completeOrder(Order $order): JsonResponse
    {
        try {
            $updatedOrder = $this->orderService->completeOrder($order);
            return response()->json([
                'status' => 'success',
                'message' => 'Đơn hàng đã hoàn thành. Doanh thu đã được ghi nhận.',
                'data' => $updatedOrder,
            ]);
        } catch (BusinessLogicException $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], 400);
        } catch (\Exception $ex) {
            return $this->genericErrorResponse(500, $ex);
        }
    }

    /**
     * Cancel order with stock return (BR-SALES-05)
     */
    public function cancelOrder(Order $order, Request $request): JsonResponse
    {
        try {
            $reason = $request->input('reason');
            $updatedOrder = $this->orderService->cancelOrder($order, $reason);
            return response()->json([
                'status' => 'success',
                'message' => 'Đơn hàng đã được hủy' . ($order->status === Order::STATUS_CONFIRMED ? ' và hoàn kho' : ''),
                'data' => $updatedOrder,
            ]);
        } catch (BusinessLogicException $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], 400);
        } catch (\Exception $ex) {
            return $this->genericErrorResponse(500, $ex);
        }
    }
}
