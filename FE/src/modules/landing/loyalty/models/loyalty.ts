/**
 * Loyalty Models
 */

export interface LoyaltyTierModel {
    id: string
    name: string
    min_points: number
    benefits: string[]
    discount_percent: number
}

export interface PointHistoryModel {
    id: number
    type: 'earn' | 'redeem'
    points: number
    description: string
    order_id?: number
    created_at: string
}

export interface LoyaltyInfoModel {
    current_points: number
    total_earned: number
    total_redeemed: number
    tier: LoyaltyTierModel
    next_tier?: LoyaltyTierModel
    points_to_next_tier?: number
}
