/**
 * Landing Auth Module
 * 
 * Folder-based organization:
 * - types/      AuthUser, Payloads
 * - helpers/    Validation, User utilities
 */

// Types
export type { AuthUser } from './types/user'
export type { LoginPayload, RegisterPayload, PasswordResetPayload, PasswordUpdatePayload } from './types/payload'

// Helpers
export { validateLoginForm, validateRegisterForm } from './helpers/validation'
export { isAdmin, getUserDisplayName, getUserAvatar } from './helpers/user'

// Re-export core auth store for convenience
export { useAuthStore } from '@/stores'
