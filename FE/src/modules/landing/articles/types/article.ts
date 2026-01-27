export interface Article {
  id: number
  title: string
  slug: string
  excerpt?: string
  content: string
  thumbnail?: string
  author?: string
  category?: string
  tags?: string[]
  views?: number
  published_at?: string
  created_at?: string
}

export interface ArticleFilters {
  search?: string
  category?: string
  tag?: string
}
