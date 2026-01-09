import type { Category } from '@/types'

export interface CategoryWithProducts extends Category {
  products_count?: number
  products?: { id: number; name: string; thumbnail?: string }[]
}
