/**
 * Users Store
 * Manages state for user management
 */

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import httpClient from '@/plugins/api/httpClient'

export interface User {
  id: number
  name: string
  email: string
  roles?: { id: number; name: string }[]
  is_active?: boolean
  created_at: string
}

export const useUserStore = defineStore('admin-users', () => {
  // State
  const users = ref<User[]>([])
  const isLoading = ref(false)
  const currentPage = ref(1)
  const totalPages = ref(1)

  // Getters
  const hasUsers = computed(() => users.value.length > 0)

  // Actions
  async function fetchUsers(params?: { page?: number; per_page?: number; search?: string }) {
    isLoading.value = true
    try {
      const queryParams: Record<string, unknown> = {
        page: params?.page || currentPage.value,
        per_page: params?.per_page || 15
      }
      if (params?.search) queryParams.search = params.search

      const response = await httpClient.get('/admin/users', { params: queryParams })
      const data = response.data as any

      if (data?.data?.data && Array.isArray(data.data.data)) {
        users.value = data.data.data
        totalPages.value = data.data.last_page || 1
        currentPage.value = data.data.current_page || 1
      } else if (Array.isArray(data?.data)) {
        users.value = data.data
        totalPages.value = 1
      } else {
        users.value = []
      }
    } catch (error) {
      console.error('Failed to fetch users:', error)
      users.value = []
    } finally {
      isLoading.value = false
    }
  }

  function setPage(page: number) {
    currentPage.value = page
  }

  function reset() {
    users.value = []
    currentPage.value = 1
  }

  return {
    // State
    users,
    isLoading,
    currentPage,
    totalPages,
    // Getters
    hasUsers,
    // Actions
    fetchUsers,
    setPage,
    reset
  }
})

