<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Exceptions\BusinessLogicException;
use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Admin\OrderRequest;
use App\Http\Requests\Modules\Admin\OrderStatusRequest;
use App\Http\Requests\Modules\Admin\AssignShipperRequest;
use App\Http\Requests\Modules\Admin\UpdateTrackingRequest;
use App\Models\Order;
use App\Services\Admin\OrderService;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    use StoreApiResponse;

    public function __construct(
        private OrderService $orderService
    ) {
    }

    /**
     * Get user orders (or all orders for admin)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $perPage = (int) $request->query('per_page', 10);

            // Check if request is from Admin API AND user has admin roles
            $isAdminRequest = $request->is('api/v1/admin/*') || $request->is('v1/admin/*');
            
            $adminRoles = ['admin', 'manager', 'super_admin'];
            $hasAdminRole = $user && (
                in_array($user->role, $adminRoles) ||
                (method_exists($user, 'roles') && $user->roles()->whereIn('name', $adminRoles)->exists())
            );

            if ($isAdminRequest && $hasAdminRole) {
                // Admin panel: sees all orders with filters
                $filters = [
                    'status' => $request->query('status'),
                    'search' => $request->query('search'),
                ];
                $orders = $this->orderService->getAll($perPage, $filters);
            } else {
                // Landing page or non-admin: always sees only their own orders
                $orders = $this->orderService->getByUserId(Auth::id(), $perPage);
            }

            return $this->paginatedResponse($orders);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
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

            return $this->successResponse($orderData);
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse('order_not_found');
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
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

            return $this->createdResponse($order, 'order_created');
        } catch (BusinessLogicException $ex) {
            return $this->errorResponse('cart_empty', null, 400);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    /**
     * Update order status
     */
    public function updateStatus(Order $order, OrderStatusRequest $request): JsonResponse
    {
        try {
            if (!$order || !$order->id) {
                return $this->notFoundResponse('order_not_found');
            }

            $order->update(['status' => $request->validated()['status']]);

            return $this->updatedResponse($order->fresh(), 'order_updated');
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse('order_not_found');
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    /**
     * Cancel order
     */
    public function cancel(Order $order): JsonResponse
    {
        try {
            if (!$order || !$order->id) {
                return $this->notFoundResponse('order_not_found');
            }

            if ($order->status === 'cancelled') {
                return $this->errorResponse('order_already_cancelled', null, 400);
            }

            if (in_array($order->status, ['delivered', 'shipped'])) {
                return $this->errorResponse('order_cannot_cancel', null, 400);
            }

            $order->update(['status' => 'cancelled']);

            return $this->successResponse($order->fresh(), 'order_cancelled');
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse('order_not_found');
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
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

            return $this->successResponse($stockInfo);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    /**
     * Assign shipper to order
     */
    public function assignShipper(Order $order, AssignShipperRequest $request): JsonResponse
    {
        try {
            $shipment = $this->orderService->assignShipper($order, $request->validated()['shipper_id']);

            return $this->successResponse($shipment, 'shipper_assigned');
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
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

            return $this->updatedResponse($shipment, 'tracking_updated');
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    /**
     * Confirm order (BR-SALES-02)
     */
    public function confirmOrder(Order $order): JsonResponse
    {
        try {
            $updatedOrder = $this->orderService->confirmOrder($order);
            return $this->successResponse($updatedOrder, 'order_confirmed');
        } catch (BusinessLogicException $ex) {
            return $this->errorResponse($ex->getMessage(), null, 400);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    /**
     * Mark order as delivered (BR-SALES-02)
     */
    public function markDelivered(Order $order): JsonResponse
    {
        try {
            $updatedOrder = $this->orderService->markDelivered($order);
            return $this->successResponse($updatedOrder, 'order_delivered');
        } catch (BusinessLogicException $ex) {
            return $this->errorResponse($ex->getMessage(), null, 400);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    /**
     * Complete order (BR-SALES-03)
     */
    public function completeOrder(Order $order): JsonResponse
    {
        try {
            $updatedOrder = $this->orderService->completeOrder($order);
            return $this->successResponse($updatedOrder, 'order_completed');
        } catch (BusinessLogicException $ex) {
            return $this->errorResponse($ex->getMessage(), null, 400);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
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
            return $this->successResponse($updatedOrder, 'order_cancelled');
        } catch (BusinessLogicException $ex) {
            return $this->errorResponse($ex->getMessage(), null, 400);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }
}


