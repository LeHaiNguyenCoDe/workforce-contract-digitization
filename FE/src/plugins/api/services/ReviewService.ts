import { BaseApiService } from './BaseApiService'
import type { ApiResponse } from '../types'

export interface Review {
  id: number
  rating: number
  content: string
  is_admin_reply?: boolean
  status: 'pending' | 'approved' | 'rejected'
  product?: { id: number; name: string }
  user?: { id: number; name: string; email: string }
  created_at: string
}

interface CreateReviewRequest {
  rating: number
  content: string
  product_id: number
}

interface UpdateReviewRequest {
  rating?: number
  content?: string
  status?: 'pending' | 'approved' | 'rejected'
}

/**
 * Admin Review Service
 */
class AdminReviewService extends BaseApiService<Review, CreateReviewRequest, UpdateReviewRequest> {
  protected readonly endpoint = '/admin/reviews'

  async approve(id: number): Promise<Review> {
    const response = await this.httpClient.put<ApiResponse<Review>>(`${this.endpoint}/${id}/approve`)
    return response.data.data!
  }

  async reject(id: number): Promise<Review> {
    const response = await this.httpClient.put<ApiResponse<Review>>(`${this.endpoint}/${id}/reject`)
    return response.data.data!
  }
}

export const adminReviewService = new AdminReviewService()

