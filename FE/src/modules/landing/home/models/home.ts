/**
 * Home Models
 */

export interface BannerModel {
    id: number
    title: string
    subtitle?: string
    image: string
    link?: string
    button_text?: string
    position: number
    is_active: boolean
}

export interface PostModel {
    id: number
    title: string
    image: string
    link: string
    slug: string
}

export interface ReviewModel {
    id: number
    image: string
    comment: string
    rating: number
    author: string
}