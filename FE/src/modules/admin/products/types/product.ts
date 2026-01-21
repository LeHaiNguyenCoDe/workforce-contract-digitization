import type { Category } from '@/types'

export interface Product {
  id: number
  name: string
  slug: string
  price: number
  sale_price?: number
  thumbnail?: string
  images?: ProductImage[]
  category?: Category
  category_id?: number
  stock_quantity?: number
  is_active?: boolean
  discount_percentage?: number
  short_description?: string
  description?: string
  rating?: {
    avg: number
    count: number
  }
  manufacturer_name?: string
  manufacturer_brand?: string
  orders_count?: number
  published_at?: string
  tags?: string[]
  visibility?: 'public' | 'private'
  created_at?: string
  updated_at?: string
  specs?: Record<string, string>
}

export interface ProductImage {
  id: number
  url: string
  alt?: string
  is_primary?: boolean
}
