/**
 * Landing Promotions Module
 */

export type { Promotion } from './types/promotion'
export { formatPromotionDiscount, isPromotionActive, isPromotionExpired, getPromotionRemainingDays, formatPromotionDateRange, calculatePromotionDiscount } from './helpers/format'
export { usePromotionStore } from './store'
