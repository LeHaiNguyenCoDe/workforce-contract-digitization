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
