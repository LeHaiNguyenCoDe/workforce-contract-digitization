/**
 * Product Entity Type
 */

import type { Category } from '@/shared/types'

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
  short_description?: string
  description?: string
  rating?: number
  reviews_count?: number
  discount_percentage?: number
  created_at?: string
  updated_at?: string
}

export interface ProductImage {
  id: number
  url: string
  alt?: string
  is_primary?: boolean
}
