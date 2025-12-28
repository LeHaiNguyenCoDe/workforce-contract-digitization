/**
 * Product Stock Helpers
 */

import type { Product } from '../types'

/**
 * Check if product is in stock
 */
export function isInStock(product: Product): boolean {
  return (product.stock_quantity ?? 0) > 0
}

/**
 * Get stock status text
 */
export function getStockStatusText(product: Product): string {
  const qty = product.stock_quantity ?? 0
  if (qty === 0) return 'Hết hàng'
  if (qty <= 5) return `Còn ${qty} sản phẩm`
  return 'Còn hàng'
}

/**
 * Get stock status color class
 */
export function getStockStatusClass(product: Product): string {
  const qty = product.stock_quantity ?? 0
  if (qty === 0) return 'text-error'
  if (qty <= 5) return 'text-warning'
  return 'text-success'
}
