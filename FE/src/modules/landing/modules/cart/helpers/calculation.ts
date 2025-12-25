/**
 * Cart Calculation Helpers
 */

import type { Cart, CartItem } from '../types'

/**
 * Calculate cart totals
 */
export function calculateCartTotals(items: CartItem[], shipping = 0, discount = 0): Cart {
  const subtotal = items.reduce((sum, item) => sum + item.price * item.quantity, 0)
  const total = Math.max(0, subtotal + shipping - discount)
  const item_count = items.reduce((sum, item) => sum + item.quantity, 0)

  return {
    items,
    subtotal,
    shipping,
    discount,
    total,
    item_count
  }
}

/**
 * Check if cart is empty
 */
export function isCartEmpty(cart: Cart): boolean {
  return cart.items.length === 0
}
