export interface Promotion {
  id: number
  title: string
  slug: string
  description?: string
  code?: string
  discount_type: 'percentage' | 'fixed'
  discount_value: number
  min_order_value?: number
  max_discount?: number
  thumbnail?: string
  start_date: string
  end_date: string
  is_active: boolean
  usage_count?: number
  usage_limit?: number
}
