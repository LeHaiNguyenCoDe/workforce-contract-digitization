import { BaseApiService } from './BaseApiService'

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
 * Frontend Promotion Service
 */
class FrontendPromotionService extends BaseApiService<Promotion, CreatePromotionRequest, UpdatePromotionRequest> {
  protected readonly endpoint = 'frontend/promotions'
}

/**
 * Admin Promotion Service
 */
class AdminPromotionService extends BaseApiService<Promotion, CreatePromotionRequest, UpdatePromotionRequest> {
  protected readonly endpoint = 'admin/promotions'
}

export const promotionService = new FrontendPromotionService()
export const adminPromotionService = new AdminPromotionService()

