/**
 * Wishlist Module Types
 */

export interface WishlistItem {
  id: number
  product_id: number
  product?: {
    id: number
    name: string
    price: number
    image?: string
    slug?: string
  }
  created_at?: string
}
