/**
 * Landing Cart Module
 * 
 * Folder-based organization:
 * - types/      CartItem, Cart, Payloads
 * - helpers/    Calculations, Formatting
 */

// Types
export type { CartItem } from './types/item'
export type { Cart } from './types/cart'
export type { AddToCartPayload, UpdateCartPayload } from './types/payload'

// Helpers
export { calculateCartTotals, isCartEmpty } from './helpers/calculation'
export { formatCartItemPrice, formatCartSubtotal, formatCartTotal, getCartItemThumbnail } from './helpers/format'

// Store
export { useCartStore } from './store'
