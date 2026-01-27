/**
 * Promotion Helpers
 */

import type { Promotion } from './types'

export function isPromotionActive(promotion: Promotion): boolean {
  if (!promotion.is_active) return false
  
  const now = new Date()
  
  if (promotion.starts_at && new Date(promotion.starts_at) > now) {
    return false
  }
  
  if (promotion.ends_at && new Date(promotion.ends_at) < now) {
    return false
  }
  
  if (promotion.usage_limit && promotion.used_count && promotion.used_count >= promotion.usage_limit) {
    return false
  }
  
  return true
}
