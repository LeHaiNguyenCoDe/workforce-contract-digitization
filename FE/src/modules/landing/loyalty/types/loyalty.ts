export interface LoyaltyPoints {
  total: number
  available: number
  pending: number
  expired: number
}

export interface LoyaltyTransaction {
  id: number
  type: 'earn' | 'redeem' | 'expire'
  points: number
  description: string
  order_id?: number
  created_at: string
}

export interface LoyaltyTier {
  name: string
  min_points: number
  benefits: string[]
  discount_percent?: number
}
