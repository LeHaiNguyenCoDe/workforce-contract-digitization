/**
 * Order Model
 */

export interface OrderItemModel {
    id: number
    product_id: number
    product_name: string
    product_thumbnail?: string
    quantity: number
    price: number
    subtotal: number
}

export interface OrderModel {
    id: number
    order_number: string
    status: 'pending' | 'processing' | 'shipped' | 'delivered' | 'cancelled'
    total_amount: number
    shipping_fee?: number
    discount?: number
    payment_method: string
    payment_status: 'pending' | 'paid' | 'failed'
    shipping_address: {
        name: string
        phone: string
        address: string
        province?: string
        district?: string
        ward?: string
    }
    items: OrderItemModel[]
    note?: string
    created_at: string
    updated_at?: string
}
