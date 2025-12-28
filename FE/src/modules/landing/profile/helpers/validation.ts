import type { UpdateProfilePayload, ChangePasswordPayload } from '../types'

export function validateProfileForm(data: UpdateProfilePayload): Record<string, string> | null {
  const errors: Record<string, string> = {}
  if (data.name !== undefined && !data.name?.trim()) {
    errors.name = 'Họ tên không được để trống'
  }
  if (data.phone !== undefined && data.phone && !/^(0|\+84)[3|5|7|8|9][0-9]{8}$/.test(data.phone.replace(/\s/g, ''))) {
    errors.phone = 'Số điện thoại không hợp lệ'
  }
  return Object.keys(errors).length > 0 ? errors : null
}

export function validatePasswordChange(data: ChangePasswordPayload): Record<string, string> | null {
  const errors: Record<string, string> = {}
  if (!data.current_password?.trim()) errors.current_password = 'Mật khẩu hiện tại là bắt buộc'
  if (!data.password?.trim()) errors.password = 'Mật khẩu mới là bắt buộc'
  else if (data.password.length < 8) errors.password = 'Mật khẩu phải có ít nhất 8 ký tự'
  if (data.password !== data.password_confirmation) errors.password_confirmation = 'Mật khẩu xác nhận không khớp'
  return Object.keys(errors).length > 0 ? errors : null
}
