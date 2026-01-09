/**
 * Loyalty Module Types
 */

export interface LoyaltyPoints {
  total: number
  available: number
  pending: number
  expired: number
}

export interface LoyaltyTransaction {
  id: number
  type: 'earn' | 'redeem' | 'expire' | 'adjust'
  points: number
  description?: string
  reference_type?: string
  reference_id?: number
  created_at: string
}

export interface LoyaltyTier {
  id: number
  name: string
  min_points: number
  discount_percent: number
  benefits?: string[]
}
