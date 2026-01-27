/**
 * Order Payload Types
 */

import type { OrderStatus } from './order'

export interface CreateOrderPayload {
  shipping_address: string
  phone: string
  notes?: string
  payment_method?: string
}

export interface OrderFilters {
  status?: OrderStatus
  from_date?: string
  to_date?: string
}
