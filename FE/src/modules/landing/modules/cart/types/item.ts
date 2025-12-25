/**
 * Cart Item Type
 */

export interface CartItem {
  id: number
  product_id: number
  quantity: number
  price: number
  product: {
    id: number
    name: string
    thumbnail?: string
    slug?: string
  }
}
