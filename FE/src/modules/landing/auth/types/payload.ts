/**
 * Auth Payload Types
 */

export interface LoginPayload {
  email: string
  password: string
  remember?: boolean
}

export interface RegisterPayload {
  name: string
  email: string
  password: string
  password_confirmation?: string
}

export interface PasswordResetPayload {
  email: string
}

export interface PasswordUpdatePayload {
  current_password: string
  password: string
  password_confirmation: string
}
