import { adminUserService } from '@/plugins/api/services/UserService'
import type { User, Role } from '@/plugins/api/types'

export interface UserForm {
  name: string
  email: string
  phone?: string
  password?: string
  role: string
  is_active: boolean
}

export const useUserStore = defineStore('admin-users', () => {
  // State
  const users = ref<User[]>([])
  const roles = ref<Role[]>([
    { id: 1, name: 'admin' },
    { id: 2, name: 'manager' },
    { id: 3, name: 'staff' },
    { id: 4, name: 'customer' }
  ])
  const isLoading = ref(false)
  const isSaving = ref(false)
  const currentPage = ref(1)
  const totalPages = ref(1)
  const userForm = ref<UserForm>({
    name: '',
    email: '',
    phone: '',
    password: '',
    role: 'customer',
    is_active: true
  })

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

      const response = await adminUserService.getAll(queryParams)
      
      users.value = response?.items || []
      totalPages.value = response?.meta?.last_page || 1
      currentPage.value = response?.meta?.current_page || 1
    } catch (error) {
      console.error('Failed to fetch users:', error)
      users.value = []
    } finally {
      isLoading.value = false
    }
  }

  async function createUser(payload: Partial<UserForm>): Promise<boolean> {
    isSaving.value = true
    try {
      await adminUserService.create(payload as any)
      await fetchUsers({ page: 1 })
      return true
    } catch (error) {
      console.error('Failed to create user:', error)
      throw error
    } finally {
      isSaving.value = false
    }
  }

  async function updateUser(id: number, payload: Partial<UserForm>): Promise<boolean> {
    isSaving.value = true
    try {
      await adminUserService.update(id, payload as any)
      await fetchUsers({ page: currentPage.value })
      return true
    } catch (error) {
      console.error('Failed to update user:', error)
      throw error
    } finally {
      isSaving.value = false
    }
  }

  async function deleteUser(id: number): Promise<boolean> {
    try {
      await adminUserService.delete(id)
      users.value = users.value.filter(u => u.id !== id)
      await fetchUsers({ page: currentPage.value })
      return true
    } catch (error) {
      console.error('Failed to delete user:', error)
      throw error
    }
  }

  async function updateUserRole(userId: number, role: string): Promise<boolean> {
    isSaving.value = true
    try {
      await adminUserService.update(userId, { role })
      await fetchUsers({ page: currentPage.value })
      return true
    } catch (error) {
      console.error('Failed to update user role:', error)
      throw error
    } finally {
      isSaving.value = false
    }
  }

  function setPage(page: number) {
    currentPage.value = page
  }

  function resetUserForm() {
    userForm.value = {
      name: '',
      email: '',
      phone: '',
      password: '',
      role: 'customer',
      is_active: true
    }
  }

  function reset() {
    users.value = []
    currentPage.value = 1
    resetUserForm()
  }

  return {
    // State
    users,
    roles,
    isLoading,
    isSaving,
    currentPage,
    totalPages,
    userForm,
    // Getters
    hasUsers,
    // Actions
    fetchUsers,
    createUser,
    updateUser,
    deleteUser,
    updateUserRole,
    setPage,
    resetUserForm,
    reset
  }
})
