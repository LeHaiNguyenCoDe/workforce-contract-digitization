import type { Category } from '@/shared/types'

export interface CategoryWithProducts extends Category {
  products_count?: number
  products?: { id: number; name: string; thumbnail?: string }[]
}
