/**
 * Wishlist Model
 */

export interface WishlistItemModel {
    id: number
    product_id: number
    product: {
        id: number
        name: string
        price: number
        sale_price?: number
        thumbnail?: string
    }
    added_at: string
}
