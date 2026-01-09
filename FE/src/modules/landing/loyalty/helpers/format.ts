import { formatNumber } from '@/utils'
import type { LoyaltyTransaction } from '../types'

export function formatPoints(points: number): string {
  return formatNumber(points)
}

export function getTransactionTypeText(type: LoyaltyTransaction['type']): string {
  return { earn: 'Tích điểm', redeem: 'Đổi điểm', expire: 'Hết hạn' }[type] || type
}

export function getTransactionTypeClass(type: LoyaltyTransaction['type']): string {
  return { earn: 'text-success', redeem: 'text-primary', expire: 'text-error' }[type] || 'text-slate-500'
}

export function getPointsToNextTier(current: number, nextTierMin: number): number {
  return Math.max(0, nextTierMin - current)
}
