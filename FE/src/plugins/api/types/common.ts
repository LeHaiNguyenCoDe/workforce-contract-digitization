/**
 * Common API response types
 */

// Standard API response wrapper
export interface ApiResponse<T> {
  status: 'success' | 'error'
  message?: string
  data?: T
  errors?: Record<string, string[]>
}

// Paginated response structure
export interface PaginatedResponse<T> {
  current_page: number
  data: T[]
  first_page_url: string
  from: number
  last_page: number
  last_page_url: string
  links: PaginationLink[]
  next_page_url: string | null
  path: string
  per_page: number
  prev_page_url: string | null
  to: number
  total: number
}

// Standardized Paginated Data (Custom structure for the project)
export interface PaginatedData<T> {
  items: T[]
  meta: {
    current_page: number
    last_page: number
    total: number
    per_page?: number
    from?: number
    to?: number
  }
}

export interface PaginationLink {
  url: string | null
  label: string
  active: boolean
}

// Query parameters for list endpoints
export interface ListParams {
  page?: number
  per_page?: number
  search?: string
  sort_by?: string
  sort_order?: 'asc' | 'desc'
  [key: string]: unknown
}
