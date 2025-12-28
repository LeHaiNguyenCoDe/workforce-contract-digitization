/**
 * Promotion Model
 */
export interface Promotion {
    id: number
    code: string
    name: string
    description?: string
    discount_type: 'percentage' | 'fixed'
    discount_value: number
    min_order_amount?: number
    max_discount_amount?: number
    usage_limit?: number
    usage_count: number
    is_active: boolean
    starts_at: string
    expires_at: string
    created_at: string
}

export interface CreatePromotionPayload {
    code: string
    name: string
    description?: string
    discount_type: 'percentage' | 'fixed'
    discount_value: number
    min_order_amount?: number
    max_discount_amount?: number
    usage_limit?: number
    starts_at: string
    expires_at: string
    is_active?: boolean
}
