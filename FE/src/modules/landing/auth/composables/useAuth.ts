/**
 * Auth Composable
 */

import { ref } from 'vue'
import { useAuthStore } from '@/stores'
import httpClient from '@/plugins/api/httpClient'

export function useAuth() {
  const authStore = useAuthStore()
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  async function login(credentials: any) {
    isLoading.value = true
    error.value = null
    try {
      const success = await authStore.login(credentials.email, credentials.password)
      if (!success) {
        throw new Error('Đăng nhập thất bại. Vui lòng kiểm tra lại thông tin.')
      }
      return true
    } catch (err: any) {
      error.value = err.message || 'Có lỗi xảy ra khi đăng nhập.'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function register(userData: any) {
    isLoading.value = true
    error.value = null
    try {
      // Direct API call for registration if store doesn't handle it well
      const response = await httpClient.post('/auth/register', userData)
      return response.status === 200 || response.status === 201
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Đăng ký thất bại. Vui lòng thử lại.'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function logout() {
    isLoading.value = true
    try {
      await authStore.logout()
      return true
    } catch (err: any) {
      console.error('Logout error:', err)
      return false
    } finally {
      isLoading.value = false
    }
  }

  async function forgotPassword(email: string) {
    isLoading.value = true
    try {
      await httpClient.post('/auth/forgot-password', { email })
      return true
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Yêu cầu đặt lại mật khẩu thất bại.'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  return {
    isLoading,
    error,
    login,
    register,
    logout,
    forgotPassword,
    isAuthenticated: authStore.isAuthenticated,
    user: authStore.user
  }
}
