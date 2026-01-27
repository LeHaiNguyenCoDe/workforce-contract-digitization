/**
 * Order Model
 */
export interface OrderItem {
    id: number
    product_id: number
    product_name: string
    quantity: number
    price: number
    subtotal: number
}

export interface Order {
    id: number
    order_code?: string
    customer_id: number
    customer_name?: string
    customer_email?: string
    customer_phone?: string
    shipping_address?: string
    total_amount: number
    discount_amount?: number
    shipping_fee?: number
    payment_method: 'cod' | 'bank' | 'momo' | 'vnpay'
    payment_status: 'pending' | 'paid' | 'refunded'
    status: OrderStatus
    notes?: string
    created_at: string
    updated_at: string
    items?: OrderItem[]
}

export type OrderStatus = 'pending' | 'confirmed' | 'processing' | 'shipping' | 'delivered' | 'cancelled'
