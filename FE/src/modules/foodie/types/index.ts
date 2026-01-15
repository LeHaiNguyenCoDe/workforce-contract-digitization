/**
 * Foodie Module Types
 */

// =============================================
// CATEGORY TYPES
// =============================================
export interface CategoryItem {
  id: number
  name: string
  slug?: string
  image: string
  icon?: string
}

// =============================================
// PRODUCT TYPES
// =============================================
export interface Product {
  id: number
  name: string
  slug?: string
  brand?: string
  price: number
  originalPrice?: number
  size?: string
  alcohol?: string
  image: string
  rating?: number
  reviewsCount?: number
}

// =============================================
// RESTAURANT TYPES
// =============================================
export interface Restaurant {
  id: number
  name: string
  slug?: string
  image: string
  rating?: number
  deliveryTime?: string
  distance?: string
  cuisines?: string[]
}

// =============================================
// BANNER TYPES
// =============================================
export interface Banner {
  id: number
  title: string
  subtitle?: string
  description?: string
  image: string
  link?: string
  buttonText?: string
}

// =============================================
// SECTION CONFIG TYPES
// =============================================
export interface SectionConfig {
  title: string
  seeAllLink?: string
  showArrows?: boolean
}
