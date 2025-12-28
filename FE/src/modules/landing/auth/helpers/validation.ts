/**
 * Auth Validation Helpers
 */

import type { LoginPayload, RegisterPayload } from '../types'

/**
 * Validate login form
 */
export function validateLoginForm(data: LoginPayload): Record<string, string> | null {
  const errors: Record<string, string> = {}

  if (!data.email?.trim()) {
    errors.email = 'Email là bắt buộc'
  } else if (!isValidEmail(data.email)) {
    errors.email = 'Email không hợp lệ'
  }

  if (!data.password?.trim()) {
    errors.password = 'Mật khẩu là bắt buộc'
  } else if (data.password.length < 6) {
    errors.password = 'Mật khẩu phải có ít nhất 6 ký tự'
  }

  return Object.keys(errors).length > 0 ? errors : null
}

/**
 * Validate register form
 */
export function validateRegisterForm(data: RegisterPayload): Record<string, string> | null {
  const errors: Record<string, string> = {}

  if (!data.name?.trim()) {
    errors.name = 'Họ tên là bắt buộc'
  }

  if (!data.email?.trim()) {
    errors.email = 'Email là bắt buộc'
  } else if (!isValidEmail(data.email)) {
    errors.email = 'Email không hợp lệ'
  }

  if (!data.password?.trim()) {
    errors.password = 'Mật khẩu là bắt buộc'
  } else if (data.password.length < 8) {
    errors.password = 'Mật khẩu phải có ít nhất 8 ký tự'
  }

  if (data.password_confirmation && data.password !== data.password_confirmation) {
    errors.password_confirmation = 'Mật khẩu xác nhận không khớp'
  }

  return Object.keys(errors).length > 0 ? errors : null
}

/**
 * Check if email is valid
 */
function isValidEmail(email: string): boolean {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email)
}
