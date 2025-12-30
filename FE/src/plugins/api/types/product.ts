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
}

export interface UpdateProductRequest {
  name?: string
  slug?: string
  price?: number
  description?: string
  short_description?: string
  category_id?: number
}
