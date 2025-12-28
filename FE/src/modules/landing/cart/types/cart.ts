/**
 * Cart Type
 */

import type { CartItem } from './item'

export interface Cart {
  items: CartItem[]
  subtotal: number
  shipping: number
  discount: number
  total: number
  item_count: number
}
