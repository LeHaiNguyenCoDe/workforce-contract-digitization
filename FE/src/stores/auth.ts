import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authService, type User } from '@/plugins/api'
import { setLocale } from '@/plugins/i18n'

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref<User | null>(null)
  const isLoading = ref(false)
  const isInitialized = ref(false)
  const cartCount = ref(0)
  
  let initPromise: Promise<void> | null = null

  // Getters
  const isAuthenticated = computed(() => !!user.value)
  
  // Check if user can access admin panel - any staff role or has dashboard permission
  const isAdmin = computed(() => {
    if (!user.value) return false
    
    // Check if user has view_dashboard permission (most flexible check)
    if (user.value.permissions?.includes('view_dashboard')) {
      return true
    }
    
    // Check simple role string
    const adminRoles = ['admin', 'manager', 'staff', 'warehouse']
    if (user.value.role && adminRoles.includes(user.value.role)) {
      return true
    }
    
    // Check roles array (Laravel typically returns this)
    if (user.value.roles && Array.isArray(user.value.roles)) {
      return user.value.roles.some(r => adminRoles.includes(r.name))
    }
    
    return false
  })
  
  const userName = computed(() => user.value?.name || '')
  const userLocale = computed(() => user.value?.language || 'vi')

  // Actions
  async function login(email: string, password: string, remember = false): Promise<boolean> {
    isLoading.value = true
    try {
      user.value = await authService.login({ email, password, remember })
      if (user.value?.language) {
        await setLocale(user.value.language as any)
      }
      console.log('Logged in user:', user.value)
      console.log('Is admin:', isAdmin.value)
      isInitialized.value = true
      return true
    } catch (error) {
      console.error('Login failed:', error)
      return false
    } finally {
      isLoading.value = false
    }
  }

  async function register(name: string, email: string, password: string): Promise<boolean> {
    isLoading.value = true
    try {
      await authService.register({ name, email, password })
      return true
    } catch (error) {
      console.error('Register failed:', error)
      return false
    } finally {
      isLoading.value = false
    }
  }

  async function logout(): Promise<void> {
    try {
      await authService.logout()
    } finally {
      user.value = null
      cartCount.value = 0
      isInitialized.value = true
    }
  }

  /**
   * Fetch current user session.
   * Uses a shared promise to prevent multiple concurrent calls and race conditions.
   */
  async function fetchUser(): Promise<void> {
    if (isInitialized.value && !isLoading.value) return
    if (initPromise) return initPromise

    initPromise = (async () => {
      isLoading.value = true
      try {
        user.value = await authService.getCurrentUser()
        if (user.value?.language) {
          await setLocale(user.value.language as any)
        }
        // console.log('Fetched user:', user.value)
        // console.log('Is admin:', isAdmin.value)
      } catch (error) {
        user.value = null
      } finally {
        isLoading.value = false
        isInitialized.value = true
        initPromise = null
      }
    })()

    return initPromise
  }

  async function updateProfile(data: Partial<User>): Promise<boolean> {
    try {
      user.value = await authService.updateProfile(data)
      return true
    } catch {
      return false
    }
  }

  function setCartCount(count: number) {
    cartCount.value = count
  }

  function incrementCartCount(amount = 1) {
    cartCount.value += amount
  }

  function decrementCartCount(amount = 1) {
    cartCount.value = Math.max(0, cartCount.value - amount)
  }

  return {
    // State
    user,
    isLoading,
    isInitialized,
    cartCount,
    // Getters
    isAuthenticated,
    isAdmin,
    userName,
    userLocale,
    // Actions
    login,
    register,
    logout,
    fetchUser,
    updateProfile,
    setCartCount,
    incrementCartCount,
    decrementCartCount
  }
})


