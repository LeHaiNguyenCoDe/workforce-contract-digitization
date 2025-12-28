import httpClient from '../httpClient'
import type { ApiResponse, User, LoginRequest, RegisterRequest, LoginResponse } from '../types'

const TOKEN_KEY = 'auth_token'

/**
 * Auth Service - handles authentication with token-based auth
 * Token is stored in localStorage and sent via Authorization header
 */
class AuthService {
  private readonly basePath = '/frontend'

  /**
   * Get stored token
   */
  getToken(): string | null {
    return localStorage.getItem(TOKEN_KEY)
  }

  /**
   * Save token to localStorage
   */
  private setToken(token: string): void {
    localStorage.setItem(TOKEN_KEY, token)
  }

  /**
   * Remove token from localStorage
   */
  private clearToken(): void {
    localStorage.removeItem(TOKEN_KEY)
  }

  async login(credentials: LoginRequest): Promise<User> {
    const response = await httpClient.post<ApiResponse<LoginResponse>>(
      `${this.basePath}/login`,
      credentials
    )
    
    // Debug: Log the full response to see structure
    console.log('Login API response:', response.data)
    
    const loginData = response.data.data!
    
    // Debug: Log login data specifically
    console.log('Login data:', loginData)
    console.log('Token in response:', loginData.token)
    
    // Save token if provided
    if (loginData.token) {
      this.setToken(loginData.token)
      console.log('Token saved to localStorage')
    } else {
      console.warn('No token in login response!')
    }
    
    return loginData.user
  }

  async register(data: RegisterRequest): Promise<User> {
    const response = await httpClient.post<ApiResponse<User>>(
      `${this.basePath}/register`,
      data
    )
    return response.data.data!
  }

  async logout(): Promise<void> {
    try {
      await httpClient.post(`${this.basePath}/logout`)
    } finally {
      // Always clear token, even if API call fails
      this.clearToken()
    }
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

