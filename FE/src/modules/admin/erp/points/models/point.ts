/**
 * Points Model
 */
export interface PointTransaction {
    id: number
    customer_id: number
    type: 'earn' | 'redeem' | 'adjust' | 'expire'
    points: number  // Changed from 'amount' to match backend
    balance_after: number
    description?: string
    order_id?: number
    created_at: string
    customer?: { id: number; name: string; email: string }
}

export interface CustomerPoints {
    customer_id: number
    customer_name: string
    customer_email: string
    current_points: number
    tier_name: string
    tier_color: string
    total_earned: number
    total_redeemed: number
}
