/**
 * Promotion Module Types
 */

export interface Promotion {
  id: number
  code: string
  name: string
  description?: string
  discount_type: 'percentage' | 'fixed'
  discount_value: number
  min_order_value?: number
  max_discount?: number
  starts_at?: string
  ends_at?: string
  is_active: boolean
  usage_limit?: number
  used_count?: number
}
