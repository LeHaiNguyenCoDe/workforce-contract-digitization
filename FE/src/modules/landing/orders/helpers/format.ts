/**
 * Order Format Helpers
 */

import { formatPrice, formatDate } from '@/utils'
import type { Order } from '../types'

/**
 * Format order total
 */
export function formatOrderTotal(order: Order): string {
  return formatPrice(order.total)
}

/**
 * Format order date
 */
export function formatOrderDate(order: Order): string {
  return formatDate(order.created_at)
}

/**
 * Get order items count
 */
export function getOrderItemsCount(order: Order): number {
  return order.items.reduce((sum, item) => sum + item.quantity, 0)
}
