/**
 * Article Model
 */

export interface ArticleModel {
    id: number
    title: string
    slug: string
    excerpt?: string
    content: string
    thumbnail?: string
    category?: string
    author?: {
        id: number
        name: string
        avatar?: string
    }
    view_count?: number
    is_published: boolean
    published_at?: string
    created_at: string
}

export interface ArticleFiltersModel {
    search?: string
    category?: string
}
