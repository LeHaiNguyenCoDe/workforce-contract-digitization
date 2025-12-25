/**
 * Landing Products Module
 * 
 * Folder-based organization for scalability:
 * - types/      Type definitions organized by domain
 * - helpers/    Helper functions organized by purpose
 * - store.ts    Pinia store
 */

// Types - from types/ folder
export type { Product, ProductImage } from './types/product'
export type { ProductFilters, ProductListParams } from './types/filters'
export type { ProductReview, CreateReviewPayload } from './types/review'

// Helpers - from helpers/ folder
export { formatProductPrice, getProductThumbnail } from './helpers/format'
export { isInStock, getStockStatusText, getStockStatusClass } from './helpers/stock'
export { getRatingStars, formatRating } from './helpers/rating'

// Store
export { useProductStore } from './store'
