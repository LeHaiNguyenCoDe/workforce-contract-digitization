/**
 * Category Module Types
 */

export interface Category {
  id: number
  name: string
  slug?: string
  description?: string
  image?: string
  parent_id?: number | null
  is_active?: boolean
  products_count?: number
}

export interface CategoryWithProducts extends Category {
  products?: Array<{
    id: number
    name: string
    slug?: string
    price: number
    image?: string
  }>
}
