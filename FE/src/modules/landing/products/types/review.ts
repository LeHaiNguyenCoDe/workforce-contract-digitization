/**
 * Product Review Types
 */

export interface ProductReview {
  id: number
  user_id: number
  user_name: string
  rating: number
  comment: string
  created_at: string
}

export interface CreateReviewPayload {
  product_id: number
  rating: number
  comment: string
}
