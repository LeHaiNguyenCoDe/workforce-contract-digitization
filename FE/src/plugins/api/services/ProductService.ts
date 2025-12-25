import { BaseApiService } from './BaseApiService'
import httpClient from '../httpClient'
import type { 
  Product, 
  CreateProductRequest, 
  UpdateProductRequest,
  ApiResponse,
  PaginatedResponse
} from '../types'

/**
 * Frontend Product Service
 * For customer-facing product operations
 */
class FrontendProductService extends BaseApiService<Product, CreateProductRequest, UpdateProductRequest> {
  protected readonly endpoint = '/frontend/products'

  /**
   * Get products by category
   */
  async getByCategory(categoryId: number, params?: { per_page?: number; page?: number }): Promise<PaginatedResponse<Product>> {
    const searchParams = new URLSearchParams()
    if (params?.per_page) searchParams.set('per_page', String(params.per_page))
    if (params?.page) searchParams.set('page', String(params.page))
    
    const query = searchParams.toString()
    const url = `/frontend/categories/${categoryId}/products${query ? `?${query}` : ''}`
    
    const response = await httpClient.get<ApiResponse<PaginatedResponse<Product>>>(url)
    return response.data.data!
  }
}

/**
 * Admin Product Service
 * For admin product management
 */
class AdminProductService extends BaseApiService<Product, CreateProductRequest, UpdateProductRequest> {
  protected readonly endpoint = '/admin/products'
}

// Export singleton instances
export const productService = new FrontendProductService()
export const adminProductService = new AdminProductService()
