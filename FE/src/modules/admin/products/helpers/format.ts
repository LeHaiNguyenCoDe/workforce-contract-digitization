import { formatPrice as sharedFormatPrice } from '@/utils'
import type { Product } from '../types'

export function formatProductPrice(product: Product): string {
  if (product.sale_price && product.sale_price < product.price) {
    return `${sharedFormatPrice(product.sale_price)} (${sharedFormatPrice(product.price)})`
  }
  return sharedFormatPrice(product.price)
}

export function formatProductStatus(product: Product): { text: string; class: string } {
  if (!product.is_active) return { text: 'Ẩn', class: 'text-error bg-error/10' }
  if ((product.stock_quantity ?? 0) === 0) return { text: 'Hết hàng', class: 'text-warning bg-warning/10' }
  return { text: 'Đang bán', class: 'text-success bg-success/10' }
}
