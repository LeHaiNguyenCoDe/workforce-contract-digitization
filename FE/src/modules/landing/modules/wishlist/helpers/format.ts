/**
 * Wishlist Format Helpers
 */

import { formatPrice } from '@/shared/utils'
import type { WishlistItem } from '../types'

export function formatWishlistItemPrice(item: WishlistItem): { original: string; sale?: string } {
  const original = formatPrice(item.product.price)
  const sale = item.product.sale_price ? formatPrice(item.product.sale_price) : undefined
  return { original, sale }
}

export function isWishlistItemInStock(item: WishlistItem): boolean {
  return (item.product.stock_quantity ?? 0) > 0
}
