/**
 * Cart Helpers
 */

import type { Cart, CartItem } from './types'

export function calculateCartTotals(items: CartItem[], shipping: number = 0, discount: number = 0): Cart {
  const subtotal = items.reduce((sum, item) => sum + (item.subtotal || item.price * item.quantity), 0)
  const total = subtotal + shipping - discount

  return {
    items,
    item_count: items.reduce((count, item) => count + item.quantity, 0),
    subtotal,
    shipping,
    discount,
    total: Math.max(0, total)
  }
}
