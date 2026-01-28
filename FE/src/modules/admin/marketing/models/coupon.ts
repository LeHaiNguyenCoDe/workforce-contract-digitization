/**
 * Coupon Management Models
 */

export interface Coupon {
    id: number
    code: string
    name: string
    description: string | null
    type: CouponType
    value: number
    min_purchase_amount: number | null
    max_discount_amount: number | null
    usage_count: number
    usage_limit_total: number | null
    usage_limit_per_user: number | null
    usage_limit_per_day: number | null
    applicable_products: number[] | null
    applicable_categories: number[] | null
    applicable_segments: number[] | null
    excluded_products: number[] | null
    bxgy_buy_quantity: number | null
    bxgy_get_quantity: number | null
    bxgy_get_products: number[] | null
    stackable: boolean
    auto_apply: boolean
    first_order_only: boolean
    is_active: boolean
    valid_from: string | null
    valid_to: string | null
    created_by: number | null
    created_at: string
    updated_at: string
}

export type CouponType = 'percentage' | 'fixed' | 'bxgy' | 'free_shipping'

export interface CouponStats {
    total_coupons: number
    active_coupons: number
    total_usages: number
    usage_rate: number
    total_discount_given: number
    by_type: Record<CouponType, number>
}

export interface CouponFilters {
    search?: string
    type?: CouponType | ''
    status?: 'active' | 'inactive' | 'expired' | ''
}

export interface CouponForm {
    code: string
    name: string
    description: string
    type: CouponType
    value: number
    min_purchase_amount: number | null
    max_discount_amount: number | null
    usage_limit_total: number | null
    usage_limit_per_user: number | null
    usage_limit_per_day: number | null
    auto_apply: boolean
    stackable: boolean
    first_order_only: boolean
    valid_from: string
    valid_to: string
}

export interface CouponValidation {
    valid: boolean
    coupon: Coupon | null
    discount_amount: number
    message: string
}
