import { BaseApiService } from './BaseApiService'
import type { Category } from '../types'

interface CreateCategoryRequest {
  name: string
  slug?: string
  description?: string
  parent_id?: number | null
}

interface UpdateCategoryRequest {
  name?: string
  slug?: string
  description?: string
  parent_id?: number | null
}

/**
 * Frontend Category Service
 */
class FrontendCategoryService extends BaseApiService<Category, CreateCategoryRequest, UpdateCategoryRequest> {
  protected readonly endpoint = 'frontend/categories'
}

/**
 * Admin Category Service
 */
class AdminCategoryService extends BaseApiService<Category, CreateCategoryRequest, UpdateCategoryRequest> {
  protected readonly endpoint = 'admin/categories'
}

export const categoryService = new FrontendCategoryService()
export const adminCategoryService = new AdminCategoryService()
