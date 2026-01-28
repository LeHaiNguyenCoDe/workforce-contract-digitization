<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Exceptions\BusinessLogicException;
use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Admin\OrderRequest;
use App\Http\Requests\Modules\Admin\OrderStatusRequest;
use App\Http\Requests\Modules\Admin\AssignShipperRequest;
use App\Http\Requests\Modules\Admin\UpdateTrackingRequest;
use App\Http\Resources\Admin\OrderResource;
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
            $isAdminRequest = $request->is('api/*/admin/*') || $request->is('api/v1/admin/*');
            
            $adminRoles = ['admin', 'manager', 'super_admin'];
            $hasAdminRole = $user && in_array($user->role, $adminRoles);

            if ($isAdminRequest && $hasAdminRole) {
                // Admin panel: sees all orders with filters
                $filters = $request->only(['status', 'search']);
                $orders = $this->orderService->getAll($perPage, $filters);
            } else {
                // Landing page or non-admin: always sees only their own orders
                $orders = $this->orderService->getByUserId(Auth::id(), $perPage);
            }

            return $this->paginatedResponse($orders, null, [], OrderResource::class);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('An error occurred while fetching orders', $ex);
        }
    }

    /**
     * Get order details
     */
    public function show(int $id): JsonResponse
    {
        try {
            $order = $this->orderService->getById($id);

            // If admin, include stock check results
            if (Auth::user() && Auth::user()->role !== 'customer') {
                $order->stock_check = $this->orderService->checkStockAvailability($order);
            }

            return $this->successResponse(new OrderResource($order));
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse($ex->getMessage());
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('An error occurred while fetching order details', $ex);
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

            return $this->createdResponse(new OrderResource($order), 'Order created successfully');
        } catch (BusinessLogicException $ex) {
            return $this->errorResponse($ex->getMessage(), null, 400);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('An error occurred while creating order', $ex);
        }
    }

    /**
     * Update order status
     */
    public function updateStatus(int $id, OrderStatusRequest $request): JsonResponse
    {
        try {
            $order = $this->orderService->updateStatus($id, $request->validated()['status']);

            return $this->updatedResponse(new OrderResource($order), 'Order status updated');
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse($ex->getMessage());
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('An error occurred while updating order status', $ex);
        }
    }

    /**
     * Cancel order (Legacy/Simple)
     */
    public function cancel(int $id): JsonResponse
    {
        try {
            $order = $this->orderService->getById($id);
            $updatedOrder = $this->orderService->cancelOrder($order);

            return $this->successResponse(new OrderResource($updatedOrder), 'Order cancelled');
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse($ex->getMessage());
        } catch (BusinessLogicException $ex) {
            return $this->errorResponse($ex->getMessage(), null, 400);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('An error occurred while cancelling order', $ex);
        }
    }

    /**
     * Check stock for an order
     */
    public function checkStock(int $id): JsonResponse
    {
        try {
            $order = $this->orderService->getById($id);
            $stockInfo = $this->orderService->checkStockAvailability($order);

            return $this->successResponse($stockInfo);
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse($ex->getMessage());
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('An error occurred while checking stock', $ex);
        }
    }

    /**
     * Assign shipper to order
     */
    public function assignShipper(int $id, AssignShipperRequest $request): JsonResponse
    {
        try {
            $order = $this->orderService->getById($id);
            $shipment = $this->orderService->assignShipper($order, $request->validated()['shipper_id']);

            return $this->successResponse($shipment, 'Shipper assigned successfully');
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse($ex->getMessage());
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('An error occurred while assigning shipper', $ex);
        }
    }

    /**
     * Update shipment tracking/GPS
     */
    public function updateTracking(int $id, UpdateTrackingRequest $request): JsonResponse
    {
        try {
            $shipment = $this->orderService->updateTracking($id, $request->validated());

            return $this->updatedResponse($shipment, 'Shipment tracking updated');
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse($ex->getMessage());
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('An error occurred while updating tracking', $ex);
        }
    }

    /**
     * Confirm order (BR-SALES-02)
     */
    public function confirmOrder(int $id): JsonResponse
    {
        try {
            $order = $this->orderService->getById($id);
            $updatedOrder = $this->orderService->confirmOrder($id);
            return $this->successResponse(new OrderResource($updatedOrder), 'Order confirmed');
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse($ex->getMessage());
        } catch (BusinessLogicException $ex) {
            return $this->errorResponse($ex->getMessage(), null, 400);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('An error occurred while confirming order', $ex);
        }
    }

    /**
     * Mark order as delivered (BR-SALES-02)
     */
    public function markDelivered(int $id): JsonResponse
    {
        try {
            $order = $this->orderService->getById($id);
            $updatedOrder = $this->orderService->markDelivered($id);
            return $this->successResponse(new OrderResource($updatedOrder), 'Order marked as delivered');
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse($ex->getMessage());
        } catch (BusinessLogicException $ex) {
            return $this->errorResponse($ex->getMessage(), null, 400);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('An error occurred while marking as delivered', $ex);
        }
    }

    /**
     * Complete order (BR-SALES-03)
     */
    public function completeOrder(int $id): JsonResponse
    {
        try {
            $order = $this->orderService->getById($id);
            $updatedOrder = $this->orderService->completeOrder($id);
            return $this->successResponse(new OrderResource($updatedOrder), 'Order completed');
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse($ex->getMessage());
        } catch (BusinessLogicException $ex) {
            return $this->errorResponse($ex->getMessage(), null, 400);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('An error occurred while completing order', $ex);
        }
    }

    /**
     * Cancel order with stock return (BR-SALES-05)
     */
    public function cancelOrder(int $id, Request $request): JsonResponse
    {
        try {
            $order = $this->orderService->getById($id);
            $reason = $request->input('reason');
            $updatedOrder = $this->orderService->cancelOrder($order, $reason);
            return $this->successResponse(new OrderResource($updatedOrder), 'Order cancelled with stock return');
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse($ex->getMessage());
        } catch (BusinessLogicException $ex) {
            return $this->errorResponse($ex->getMessage(), null, 400);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('An error occurred while cancelling order', $ex);
        }
    }
}


