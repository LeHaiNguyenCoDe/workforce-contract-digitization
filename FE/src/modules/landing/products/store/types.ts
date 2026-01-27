/**
 * Product Module Types
 */

export interface Product {
  id: number
  name: string
  slug: string
  sku?: string
  description?: string
  price: number
  sale_price?: number
  image?: string
  images?: string[]
  category_id?: number
  category?: {
    id: number
    name: string
    slug?: string
  }
  stock_qty?: number
  is_active: boolean
  is_featured?: boolean
  created_at?: string
}

export interface ProductFilters {
  search?: string
  category?: string | number
  min_price?: number
  max_price?: number
  sort?: string
}
