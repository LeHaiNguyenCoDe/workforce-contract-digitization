/**
 * Products Module
 * Admin Products Module
 * 
 * Folder-based organization:
 * - types/      Product, ProductFormData, ProductFilters
 * - helpers/    Format, Validation
 */

// Types
export type { Product, ProductImage } from './types/product'
export type { ProductFormData, ProductFilters } from './types/form'

// Helpers
export { formatProductPrice, formatProductStatus } from './helpers/format'
export { validateProductForm, mapFormToPayload, mapProductToForm } from './helpers/validation'

// Store
export { useProductStore } from './store'
