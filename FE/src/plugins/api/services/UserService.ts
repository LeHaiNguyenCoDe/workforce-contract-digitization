import { BaseApiService } from './BaseApiService'
import type { User } from '../types'

interface CreateUserRequest {
  name: string
  email: string
  password: string
  role?: string
}

interface UpdateUserRequest {
  name?: string
  email?: string
  password?: string
  role?: string
  active?: boolean
}

/**
 * Admin User Service
 */
class AdminUserService extends BaseApiService<User, CreateUserRequest, UpdateUserRequest> {
  protected readonly endpoint = '/admin/users'
}

export const adminUserService = new AdminUserService()
