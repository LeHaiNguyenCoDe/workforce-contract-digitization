/**
 * Auth User Helpers
 */

import type { AuthUser } from '../types'

/**
 * Check if user has admin role
 */
export function isAdmin(user: AuthUser | null): boolean {
  if (!user) return false
  
  if (user.role === 'admin' || user.role === 'manager') {
    return true
  }
  
  if (user.roles?.some(r => r.name === 'admin' || r.name === 'manager')) {
    return true
  }
  
  return false
}

/**
 * Get user display name
 */
export function getUserDisplayName(user: AuthUser | null): string {
  if (!user) return 'Khách'
  return user.name || user.email.split('@')[0]
}

/**
 * Get user avatar or initial
 */
export function getUserAvatar(user: AuthUser | null): { url?: string; initial: string } {
  if (!user) return { initial: 'K' }
  
  return {
    url: user.avatar,
    initial: user.name?.charAt(0).toUpperCase() || user.email.charAt(0).toUpperCase()
  }
}
