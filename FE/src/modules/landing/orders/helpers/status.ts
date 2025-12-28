/**
 * Order Status Helpers
 */

import type { Order, OrderStatus } from '../types'

/**
 * Get order status display text (Vietnamese)
 */
export function getOrderStatusText(status: OrderStatus): string {
  const statusMap: Record<OrderStatus, string> = {
    pending: 'Chờ xử lý',
    processing: 'Đang xử lý',
    shipped: 'Đang giao',
    delivered: 'Đã giao',
    cancelled: 'Đã hủy'
  }
  return statusMap[status] || status
}

/**
 * Get order status color class
 */
export function getOrderStatusClass(status: OrderStatus): string {
  const classMap: Record<OrderStatus, string> = {
    pending: 'bg-warning/10 text-warning',
    processing: 'bg-info/10 text-info',
    shipped: 'bg-primary/10 text-primary',
    delivered: 'bg-success/10 text-success',
    cancelled: 'bg-error/10 text-error'
  }
  return classMap[status] || 'bg-slate-100 text-slate-600'
}

/**
 * Check if order can be cancelled
 */
export function canCancelOrder(order: Order): boolean {
  return order.status === 'pending' || order.status === 'processing'
}
