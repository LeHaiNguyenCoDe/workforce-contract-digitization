/**
 * Cart Item Model
 */

export interface CartItemModel {
    id: number
    product_id: number
    product: {
        id: number
        name: string
        price: number
        sale_price?: number
        thumbnail?: string
    }
    quantity: number
    subtotal: number
}

export interface CartModel {
    items: CartItemModel[]
    total: number
    itemCount: number
}
