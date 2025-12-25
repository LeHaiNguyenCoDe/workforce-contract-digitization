import { generateSlug } from '@/shared/utils'
import type { ProductFormData, Product } from '../types'

export function validateProductForm(data: ProductFormData): Record<string, string> | null {
  const errors: Record<string, string> = {}
  if (!data.name?.trim()) errors.name = 'Tên sản phẩm là bắt buộc'
  if (!data.slug?.trim()) errors.slug = 'Slug là bắt buộc'
  if (!data.category_id) errors.category_id = 'Danh mục là bắt buộc'
  if (!data.price || data.price <= 0) errors.price = 'Giá phải lớn hơn 0'
  if (data.sale_price && data.sale_price >= (data.price || 0)) {
    errors.sale_price = 'Giá khuyến mãi phải nhỏ hơn giá gốc'
  }
  return Object.keys(errors).length > 0 ? errors : null
}

export function mapFormToPayload(form: ProductFormData): Record<string, unknown> {
  return {
    name: form.name,
    slug: form.slug || generateSlug(form.name),
    category_id: form.category_id,
    price: form.price,
    sale_price: form.sale_price || null,
    stock_quantity: form.stock_quantity || 0,
    short_description: form.short_description,
    description: form.description,
    is_active: form.is_active,
    thumbnail: form.thumbnail,
    images: form.images
  }
}

export function mapProductToForm(product: Product): ProductFormData {
  return {
    name: product.name,
    slug: product.slug,
    category_id: product.category_id || null,
    price: product.price,
    sale_price: product.sale_price || null,
    stock_quantity: product.stock_quantity || null,
    short_description: product.short_description || '',
    description: product.description || '',
    is_active: product.is_active ?? true,
    thumbnail: product.thumbnail,
    images: product.images?.map(i => i.url) || []
  }
}
