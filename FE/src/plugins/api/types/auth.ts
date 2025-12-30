/**
 * Auth related types
 */

export interface Role {
  id: number
  name: string
  description?: string
}

export interface User {
  id: number
  name: string
  email: string
  active: boolean
  language: 'vi' | 'en'
  role?: 'admin' | 'manager' | 'customer'  // Simple role string (if API sends it)
  roles?: Role[]  // Array of roles (Laravel typically sends this)
  permissions?: string[]  // Array of permission names from user's roles
  created_at?: string
  updated_at?: string
}

export interface LoginRequest {
  email: string
  password: string
  remember?: boolean
}

export interface RegisterRequest {
  name: string
  email: string
  password: string
}

export interface LoginResponse {
  user: User
  token?: string
  session_id?: string
}
