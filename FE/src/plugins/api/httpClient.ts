import axios, { type AxiosInstance, type AxiosRequestConfig, type AxiosResponse } from 'axios'

/**
 * HTTP Client singleton with interceptors
 */
class HttpClient {
  private static instance: HttpClient
  private axiosInstance: AxiosInstance

  private constructor() {
    // Sử dụng relative path để Vite proxy forward requests
    // Điều này giúp session/cookies hoạt động đúng khi truy cập từ network
    const apiPrefix = import.meta.env.VITE_API_PREFIX || '/api/v1'
    
    this.axiosInstance = axios.create({
      baseURL: apiPrefix,
      timeout: 30000,
      withCredentials: true,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      }
    })

    this.setupInterceptors()
  }

  static getInstance(): HttpClient {
    if (!HttpClient.instance) {
      HttpClient.instance = new HttpClient()
    }
    return HttpClient.instance
  }

  /**
   * Helper to get cookie value by name
   */
  private getCookie(name: string): string | null {
    const value = `; ${document.cookie}`
    const parts = value.split(`; ${name}=`)
    if (parts.length === 2) {
      return parts.pop()?.split(';').shift() || null
    }
    return null
  }

  private setupInterceptors(): void {
    // Request interceptor
    this.axiosInstance.interceptors.request.use(
      (config) => {
        // Add locale from localStorage
        const locale = localStorage.getItem('locale') || 'vi'
        config.headers['Accept-Language'] = locale
        
        // Add Authorization header with Bearer token if available
        const token = localStorage.getItem('auth_token')
        if (token) {
          config.headers['Authorization'] = `Bearer ${token}`
        }
        
        // Add XSRF-TOKEN from cookie for Laravel Sanctum (fallback for session-based auth)
        const xsrfToken = this.getCookie('XSRF-TOKEN')
        if (xsrfToken) {
          config.headers['X-XSRF-TOKEN'] = decodeURIComponent(xsrfToken)
        }
        
        return config
      },
      (error) => Promise.reject(error)
    )

    // Response interceptor
    this.axiosInstance.interceptors.response.use(
      (response) => response,
      (error) => {
        if (error.response) {
          const { status } = error.response
          
          switch (status) {
            case 401:
              // Emit unauthorized event
              window.dispatchEvent(new CustomEvent('auth:unauthorized'))
              break
            case 403:
              window.dispatchEvent(new CustomEvent('auth:forbidden'))
              break
            case 422:
              // Validation errors handled by caller
              break
            case 500:
              console.error('Server error:', error.response.data?.message)
              break
          }
        }
        return Promise.reject(error)
      }
    )
  }

  get<T>(url: string, config?: AxiosRequestConfig): Promise<AxiosResponse<T>> {
    return this.axiosInstance.get<T>(url, config)
  }

  post<T>(url: string, data?: unknown, config?: AxiosRequestConfig): Promise<AxiosResponse<T>> {
    return this.axiosInstance.post<T>(url, data, config)
  }

  put<T>(url: string, data?: unknown, config?: AxiosRequestConfig): Promise<AxiosResponse<T>> {
    return this.axiosInstance.put<T>(url, data, config)
  }

  patch<T>(url: string, data?: unknown, config?: AxiosRequestConfig): Promise<AxiosResponse<T>> {
    return this.axiosInstance.patch<T>(url, data, config)
  }

  delete<T>(url: string, config?: AxiosRequestConfig): Promise<AxiosResponse<T>> {
    return this.axiosInstance.delete<T>(url, config)
  }
}

export const httpClient = HttpClient.getInstance()
export default httpClient
