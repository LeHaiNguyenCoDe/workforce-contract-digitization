/**
 * Promotion Model
 */

export interface PromotionModel {
    id: number
    code: string
    name: string
    description?: string
    discount_type: 'percent' | 'fixed'
    discount_value: number
    min_order_amount?: number
    max_discount?: number
    start_date: string
    end_date: string
    is_active: boolean
    usage_limit?: number
    usage_count?: number
}
