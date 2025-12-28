/**
 * Product Filters & Params Types
 */

export interface ProductFilters {
  search?: string
  category_id?: number
  min_price?: number
  max_price?: number
  sort_by?: 'price' | 'name' | 'created_at' | 'rating'
  sort_order?: 'asc' | 'desc'
}

export interface ProductListParams extends ProductFilters {
  page?: number
  per_page?: number
}
