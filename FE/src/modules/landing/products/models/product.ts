/**
 * Landing Product Models
 */

export interface ProductModel {
    id: number
    name: string
    slug: string
    price: number
    sale_price?: number
    thumbnail?: string
    images?: string[]
    short_description?: string
    description?: string
    category_id?: number
    category?: {
        id: number
        name: string
        slug: string
    }
    rating?: number
    review_count?: number
    stock_quantity?: number
    is_active: boolean
    created_at?: string
}

export interface ProductFiltersModel {
    search?: string
    category_id?: number
    min_price?: number
    max_price?: number
    sort_by?: string
    sort_order?: 'asc' | 'desc'
}

export interface ProductCartItem extends ProductModel {
    quantity: number
}
