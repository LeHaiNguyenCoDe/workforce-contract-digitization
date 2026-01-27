/**
 * Product related types
 */

export interface Product {
  id: number
  name: string
  slug: string
  price: number
  sale_price?: number
  thumbnail?: string
  short_description?: string
  description?: string
  category?: Category
  category_id?: number
  is_active?: boolean
  stock_quantity?: number
  images?: ProductImage[]
  variants?: ProductVariant[]
  rating?: ProductRating
  manufacturer_name?: string
  manufacturer_brand?: string
  discount_percentage?: number
  orders_count?: number
  published_at?: string
  tags?: string[]
  visibility?: 'public' | 'private'
  created_at?: string
  updated_at?: string
}

export interface ProductImage {
  id: number
  url: string
  is_main: boolean
}

export interface ProductVariant {
  id: number
  color?: string
  size?: string
  stock: number
  price?: number
}

export interface ProductRating {
  avg: number
  count: number
}

export interface Category {
  id: number
  name: string
  slug?: string
  description?: string
  image?: string
  parent_id?: number | null
  children?: Category[]
}

export interface CreateProductRequest {
  name: string
  slug?: string
  price: number
  description?: string
  short_description?: string
  category_id: number
  stock_quantity?: number
  is_active?: boolean
  manufacturer_name?: string
  manufacturer_brand?: string
  discount_percentage?: number
  published_at?: string
  tags?: string[]
  visibility?: 'public' | 'private'
}

export interface UpdateProductRequest {
  name?: string
  slug?: string
  price?: number
  description?: string
  short_description?: string
  category_id?: number
  stock_quantity?: number
  is_active?: boolean
  manufacturer_name?: string
  manufacturer_brand?: string
  discount_percentage?: number
  published_at?: string
  tags?: string[]
  visibility?: 'public' | 'private'
}
