/**
 * Order Type
 */

export type OrderStatus = 'pending' | 'processing' | 'shipped' | 'delivered' | 'cancelled'

export interface OrderItem {
  id: number
  product_id: number
  product_name: string
  product_thumbnail?: string
  quantity: number
  price: number
  total: number
}

export interface Order {
  id: number
  order_number: string
  status: OrderStatus
  subtotal: number
  shipping: number
  discount: number
  total: number
  shipping_address: string
  phone: string
  notes?: string
  payment_method?: string
  items: OrderItem[]
  created_at: string
  updated_at?: string
}
