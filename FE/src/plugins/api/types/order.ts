/**
 * Order related types
 */

export interface Order {
  id: number
  order_number: string
  status: OrderStatus
  total: number
  shipping_address: string
  shipping_phone: string
  payment_method: string
  notes?: string
  items: OrderItem[]
  user_id?: number
  created_at: string
  updated_at: string
}

export type OrderStatus = 'pending' | 'processing' | 'shipped' | 'delivered' | 'cancelled'

export interface OrderItem {
  id: number
  product_id: number
  product_name?: string
  qty: number
  price: number
  subtotal: number
}

export interface CreateOrderRequest {
  shipping_address: string
  shipping_phone: string
  payment_method: string
  notes?: string
}

export interface UpdateOrderRequest {
  status?: OrderStatus
  shipping_address?: string
  shipping_phone?: string
  notes?: string
}

// Cart types
export interface Cart {
  id?: number
  items: CartItem[]
  total: number
  item_count: number
}

export interface CartItem {
  id: number
  product_id: number
  product_variant_id?: number
  product: {
    id: number
    name: string
    thumbnail?: string
  }
  qty: number
  price: number
  subtotal: number
}

export interface AddToCartRequest {
  product_id: number
  product_variant_id?: number
  qty: number
}
