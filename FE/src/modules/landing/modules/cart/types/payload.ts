/**
 * Cart Payload Types
 */

export interface AddToCartPayload {
  product_id: number
  quantity: number
  variant_id?: number
}

export interface UpdateCartPayload {
  quantity: number
}
