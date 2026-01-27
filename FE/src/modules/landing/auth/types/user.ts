/**
 * Auth User Type
 */

export interface AuthUser {
  id: number
  name: string
  email: string
  phone?: string
  avatar?: string
  language?: string
  role?: string
  roles?: { id: number; name: string }[]
  created_at?: string
}
