import { BaseApiService } from './BaseApiService'
import httpClient from '../httpClient'
import type { 
  Order, 
  CreateOrderRequest, 
  UpdateOrderRequest,
  Cart,
  AddToCartRequest,
  ApiResponse
} from '../types'

/**
 * Frontend Order Service
 */
class FrontendOrderService extends BaseApiService<Order, CreateOrderRequest, UpdateOrderRequest> {
  protected readonly endpoint = 'frontend/orders'
}

/**
 * Admin Order Service
 */
class AdminOrderService extends BaseApiService<Order, CreateOrderRequest, UpdateOrderRequest> {
  protected readonly endpoint = 'admin/orders'

  /**
   * Get orders for a specific user
   */
  async getByUser(userId: number): Promise<Order[]> {
    const response = await httpClient.get<ApiResponse<Order[]>>(`admin/users/${userId}/orders`)
    return response.data.data || []
  }
}

/**
 * Cart Service
 */
class CartService {
  private readonly basePath = 'frontend/cart'
  async getCart(): Promise<Cart> {
    const response = await httpClient.get<ApiResponse<Cart>>(this.basePath)
    return response.data.data!
  }

  async addItem(data: AddToCartRequest): Promise<void> {
    await httpClient.post(`${this.basePath}/items`, data)
  }

  async updateItem(itemId: number, qty: number): Promise<void> {
    await httpClient.put(`${this.basePath}/items/${itemId}`, { qty })
  }

  async removeItem(itemId: number): Promise<void> {
    await httpClient.delete(`${this.basePath}/items/${itemId}`)
  }

  async clear(): Promise<void> {
    await httpClient.delete(this.basePath)
  }
}

export const orderService = new FrontendOrderService()
export const adminOrderService = new AdminOrderService()
export const cartService = new CartService()
