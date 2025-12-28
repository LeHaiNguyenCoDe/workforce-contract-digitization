/**
 * Product Format Helpers
 */

import { formatPrice as sharedFormatPrice } from '@/shared/utils'
import type { Product } from '../types'

/**
 * Format product price with discount calculation
 */
export function formatProductPrice(product: Product): {
  original: string
  sale?: string
  discount?: number
} {
  const original = sharedFormatPrice(product.price)
  
  if (product.sale_price && product.sale_price < product.price) {
    const sale = sharedFormatPrice(product.sale_price)
    const discount = Math.round((1 - product.sale_price / product.price) * 100)
    return { original, sale, discount }
  }
  
  return { original }
}

/**
 * Get product thumbnail or placeholder
 */
export function getProductThumbnail(product: Product): string {
  if (product.thumbnail) return product.thumbnail
  if (product.images?.length) return product.images[0].url
  return '/placeholder-product.jpg'
}
