import httpClient from '../httpClient'
import type { ApiResponse, User, LoginRequest, RegisterRequest, LoginResponse } from '../types'

/**
 * Auth Service - handles authentication
 * Does not extend BaseApiService as auth has different patterns
 */
class AuthService {
  private readonly basePath = '/frontend'

  async login(credentials: LoginRequest): Promise<User> {
    const response = await httpClient.post<ApiResponse<LoginResponse>>(
      `${this.basePath}/login`,
      credentials
    )
    return response.data.data!.user
  }

  async register(data: RegisterRequest): Promise<User> {
    const response = await httpClient.post<ApiResponse<User>>(
      `${this.basePath}/register`,
      data
    )
    return response.data.data!
  }

  async logout(): Promise<void> {
    await httpClient.post(`${this.basePath}/logout`)
  }

  async getCurrentUser(): Promise<User> {
    const response = await httpClient.get<ApiResponse<User>>(`${this.basePath}/me`)
    return response.data.data!
  }

  async getProfile(): Promise<User> {
    const response = await httpClient.get<ApiResponse<User>>(`${this.basePath}/profile`)
    return response.data.data!
  }

  async updateProfile(data: Partial<User>): Promise<User> {
    const response = await httpClient.put<ApiResponse<User>>(
      `${this.basePath}/profile`,
      data
    )
    return response.data.data!
  }

  async setLanguage(locale: 'vi' | 'en'): Promise<void> {
    await httpClient.post(`${this.basePath}/language`, { locale })
    localStorage.setItem('locale', locale)
  }
}

export const authService = new AuthService()
export default authService
