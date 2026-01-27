export interface UserProfile {
  id: number
  name: string
  email: string
  phone?: string
  avatar?: string
  address?: string
  language?: string
  created_at?: string
}

export interface UpdateProfilePayload {
  name?: string
  phone?: string
  address?: string
  avatar?: string
  language?: string
}

export interface ChangePasswordPayload {
  current_password: string
  password: string
  password_confirmation: string
}
