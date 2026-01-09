/**
 * Cart Module Types
 */

export interface CartItem {
  id: number
  product_id: number
  product?: {
    id: number
    name: string
    price: number
    image?: string
    slug?: string
  }
  quantity: number
  price: number
  subtotal: number
}

export interface Cart {
  items: CartItem[]
  item_count: number
  subtotal: number
  shipping: number
  discount: number
  total: number
}

export interface AddToCartPayload {
  product_id: number
  quantity: number
  variant_id?: number
}

export interface UpdateCartPayload {
  quantity: number
}
