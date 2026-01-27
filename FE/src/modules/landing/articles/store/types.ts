/**
 * Article Module Types
 */

export interface Article {
  id: number
  title: string
  slug: string
  content: string
  excerpt?: string
  featured_image?: string
  author?: {
    id: number
    name: string
  }
  category?: {
    id: number
    name: string
  }
  published_at?: string
  created_at: string
  updated_at?: string
}

export interface ArticleFilters {
  search?: string
  category?: string | number
}
