import { formatPrice, formatDate } from '@/utils'
import type { Promotion } from '../types'

export function formatPromotionDiscount(promotion: Promotion): string {
  return promotion.discount_type === 'percentage' ? `-${promotion.discount_value}%` : `-${formatPrice(promotion.discount_value)}`
}

export function isPromotionActive(promotion: Promotion): boolean {
  if (!promotion.is_active) return false
  const now = new Date()
  return now >= new Date(promotion.start_date) && now <= new Date(promotion.end_date)
}

export function isPromotionExpired(promotion: Promotion): boolean {
  return new Date() > new Date(promotion.end_date)
}

export function getPromotionRemainingDays(promotion: Promotion): number {
  const diff = new Date(promotion.end_date).getTime() - new Date().getTime()
  return Math.max(0, Math.ceil(diff / (1000 * 60 * 60 * 24)))
}

export function formatPromotionDateRange(promotion: Promotion): string {
  return `${formatDate(promotion.start_date)} - ${formatDate(promotion.end_date)}`
}

export function calculatePromotionDiscount(promotion: Promotion, orderValue: number): number {
  if (promotion.min_order_value && orderValue < promotion.min_order_value) return 0
  let discount = promotion.discount_type === 'percentage' ? (orderValue * promotion.discount_value) / 100 : promotion.discount_value
  if (promotion.max_discount) discount = Math.min(discount, promotion.max_discount)
  return discount
}
