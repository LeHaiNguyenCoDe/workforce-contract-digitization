/**
 * Shared Types
 * Common types used across multiple modules
 */

/**
 * Base entity interface
 */
export interface BaseEntity {
  id: number
  created_at?: string
  updated_at?: string
}

/**
 * Category - used by Products, Articles
 */
export interface Category extends BaseEntity {
  name: string
  slug: string
  description?: string
  image?: string
  parent_id?: number | null
  is_active?: boolean
  children?: Category[]
}

/**
 * User role
 */
export interface Role {
  id: number
  name: string
}

/**
 * Basic user info (for display purposes)
 */
export interface UserBasic extends BaseEntity {
  name: string
  email: string
  avatar?: string
}

/**
 * Select option for dropdowns
 */
export interface SelectOption<T = string | number> {
  value: T
  label: string
  disabled?: boolean
}

/**
 * Table column definition
 */
export interface TableColumn<T = unknown> {
  key: keyof T | string
  label: string
  sortable?: boolean
  width?: string
  align?: 'left' | 'center' | 'right'
}
