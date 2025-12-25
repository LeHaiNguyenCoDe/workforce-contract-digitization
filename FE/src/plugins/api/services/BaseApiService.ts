import httpClient from '../httpClient'
import type { ApiResponse, PaginatedResponse, ListParams } from '../types'

/**
 * Abstract Base API Service
 * Provides common CRUD operations for all API services
 * 
 * @template T - Entity type (e.g., Product, Order)
 * @template CreateDTO - Create request type
 * @template UpdateDTO - Update request type
 */
export abstract class BaseApiService<
  T,
  CreateDTO = Partial<T>,
  UpdateDTO = Partial<T>
> {
  /**
   * API endpoint path (e.g., '/frontend/products', '/admin/products')
   */
  protected abstract readonly endpoint: string

  /**
   * Get paginated list of entities
   */
  async getAll(params?: ListParams): Promise<PaginatedResponse<T>> {
    const searchParams = new URLSearchParams()
    
    if (params) {
      Object.entries(params).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
          searchParams.set(key, String(value))
        }
      })
    }

    const queryString = searchParams.toString()
    const url = queryString ? `${this.endpoint}?${queryString}` : this.endpoint
    
    const response = await httpClient.get<ApiResponse<PaginatedResponse<T>>>(url)
    return response.data.data!
  }

  /**
   * Get single entity by ID
   */
  async getById(id: number | string): Promise<T> {
    const response = await httpClient.get<ApiResponse<T>>(`${this.endpoint}/${id}`)
    return response.data.data!
  }

  /**
   * Create new entity
   */
  async create(data: CreateDTO): Promise<T> {
    const response = await httpClient.post<ApiResponse<T>>(this.endpoint, data)
    return response.data.data!
  }

  /**
   * Update existing entity
   */
  async update(id: number | string, data: UpdateDTO): Promise<T> {
    const response = await httpClient.put<ApiResponse<T>>(`${this.endpoint}/${id}`, data)
    return response.data.data!
  }

  /**
   * Delete entity
   */
  async delete(id: number | string): Promise<void> {
    await httpClient.delete(`${this.endpoint}/${id}`)
  }
}
