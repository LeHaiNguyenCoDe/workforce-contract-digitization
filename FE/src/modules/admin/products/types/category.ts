/**
 * Category Type (Part of Products Module)
 */
export interface Category {
    id: number
    name: string
    slug: string
    description?: string
    parent_id?: number
    image?: string
    is_active: boolean
    products_count?: number
    created_at: string
    updated_at: string
    children?: Category[]
}

export interface CreateCategoryPayload {
    name: string
    slug?: string
    description?: string
    parent_id?: number
    is_active?: boolean
}
