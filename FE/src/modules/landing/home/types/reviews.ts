export interface ProductImage {
  id: number
  product_id: number
  image_url: string
  is_main: boolean
}

export interface Product {
  id: number
  name: string
  slug?: string
  thumbnail?: string
  images?: ProductImage[]
}

export interface User {
  id: number
  name: string
}

export interface Review {
  id: number
  product_id: number
  user_id: number
  rating: number
  content: string
  parent_id?: number | null
  is_admin_reply?: boolean
  user?: User
  product?: Product
  replies?: Review[]
  created_at: string
  updated_at: string
}

export interface PaginationMeta {
  current_page: number
  from: number | null
  last_page: number
  per_page: number
  to: number | null
  total: number
}

export interface ReviewsResponse {
  items: Review[]
  meta: PaginationMeta
}

export interface ProductsResponse {
  items: Product[]
  meta: PaginationMeta
}

export interface FeaturedReview {
  id: number
  image: string
  comment: string
  rating: number
  author: string
}