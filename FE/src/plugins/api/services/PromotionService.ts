import { BaseApiService } from './BaseApiService'
import type { ApiResponse } from '../types'

export interface Promotion {
  id: number
  name: string
  code?: string
  type: 'percent' | 'fixed_amount'
  value: number
  starts_at?: string
  ends_at?: string
  is_active?: boolean
}

interface CreatePromotionRequest {
  name: string
  code?: string
  type: 'percent' | 'fixed_amount'
  value: number
  starts_at?: string
  ends_at?: string
  is_active?: boolean
}

interface UpdatePromotionRequest {
  name?: string
  code?: string
  type?: 'percent' | 'fixed_amount'
  value?: number
  starts_at?: string
  ends_at?: string
  is_active?: boolean
}

/**
 * Admin Promotion Service
 */
class AdminPromotionService extends BaseApiService<Promotion, CreatePromotionRequest, UpdatePromotionRequest> {
  protected readonly endpoint = 'admin/promotions'
}

export const adminPromotionService = new AdminPromotionService()

