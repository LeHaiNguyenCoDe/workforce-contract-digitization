/**
 * Category Model
 */

export interface CategoryModel {
    id: number
    name: string
    slug: string
    description?: string
    image?: string
    parent_id?: number
    children?: CategoryModel[]
    product_count?: number
    is_active: boolean
}
