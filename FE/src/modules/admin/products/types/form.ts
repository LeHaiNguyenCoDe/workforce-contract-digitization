export interface ProductFormData {
  name: string
  slug: string
  category_id: number | null
  price: number | null
  sale_price?: number | null
  stock_quantity: number | null
  short_description: string
  description: string
  is_active: boolean
  thumbnail?: string
  images?: string[]
}

export interface ProductFilters {
  search?: string
  category_id?: number
  is_active?: boolean
}
