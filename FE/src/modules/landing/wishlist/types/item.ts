/**
 * Wishlist Item Type
 */

export interface WishlistItem {
  id: number
  product_id: number
  product: {
    id: number
    name: string
    slug: string
    price: number
    sale_price?: number
    thumbnail?: string
    stock_quantity?: number
  }
  added_at: string
}
