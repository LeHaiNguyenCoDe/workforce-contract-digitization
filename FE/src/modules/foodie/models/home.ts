/**
 * Foodie Home Models
 */

export interface BannerModel {
  id: number
  title: string
  subtitle?: string
  image: string
  link?: string
  buttonText?: string
}

export interface CategoryModel {
  id: number
  name: string
  slug: string
  icon?: string
  image?: string
}

export interface DrinkModel {
  id: number
  name: string
  slug: string
  price: number
  originalPrice?: number
  image: string
  rating?: number
  reviewsCount?: number
  category?: CategoryModel
}

export interface RestaurantModel {
  id: number
  name: string
  slug: string
  image: string
  rating: number
  deliveryTime: string
  distance?: string
  cuisines?: string[]
}

export interface PartyItemModel {
  id: number
  name: string
  slug: string
  image: string
  price: number
  originalPrice?: number
}
