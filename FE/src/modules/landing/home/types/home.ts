import type { Product } from '../../products/types'
import type { CategoryWithProducts } from '../../categories/types'
import type { Promotion } from '../../promotions/types'

export interface HomePageData {
  featuredProducts: Product[]
  newProducts: Product[]
  categories: CategoryWithProducts[]
  promotions: Promotion[]
  banners?: HomeBanner[]
}

export interface HomeBanner {
  id: number
  title?: string
  image: string
  link?: string
  order?: number
}
