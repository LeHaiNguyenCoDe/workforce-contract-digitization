/**
 * Cart Format Helpers
 */

import { formatPrice } from '@/shared/utils'
import type { Cart, CartItem } from '../types'

/**
 * Format cart item price
 */
export function formatCartItemPrice(item: CartItem): string {
  return formatPrice(item.price * item.quantity)
}

/**
 * Format cart subtotal
 */
export function formatCartSubtotal(cart: Cart): string {
  return formatPrice(cart.subtotal)
}

/**
 * Format cart total
 */
export function formatCartTotal(cart: Cart): string {
  return formatPrice(cart.total)
}

/**
 * Get cart item thumbnail
 */
export function getCartItemThumbnail(item: CartItem): string {
  return item.product?.thumbnail || '/placeholder-product.jpg'
}
