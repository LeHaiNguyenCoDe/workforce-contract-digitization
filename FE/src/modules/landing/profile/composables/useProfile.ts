/**
 * Profile Composable
 * Handles profile information, password updates, and address management
 */

import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores'
import httpClient from '@/plugins/api/httpClient'

export function useProfile() {
  const { t } = useI18n()
  const store = useAuthStore()
  const isEditing = ref(false)
  const isSaving = ref(false)
  const isLoading = ref(false)
  const message = ref('')

  // Computed
  const user = computed(() => store.user)

  // Methods
  function startEdit() {
    isEditing.value = true
  }

  function cancelEdit() {
    isEditing.value = false
    message.value = ''
  }

  async function fetchProfile() {
    isLoading.value = true
    try {
      const response = await httpClient.get('/frontend/profile')
      const data = response.data as any
      if (data?.data) {
        store.setUser(data.data)
      }
      return data?.data || data
    } catch (error) {
      console.error('Failed to fetch profile:', error)
      throw error
    } finally {
      isLoading.value = false
    }
  }

  async function updateProfile(data: any) {
    isSaving.value = true
    message.value = ''
    try {
      const response = await httpClient.put('/frontend/profile', data)
      const updatedUser = (response.data as any)?.data || response.data
      store.setUser(updatedUser)
      isEditing.value = false
      message.value = t('common.updateInfoSuccess')
      return true
    } catch (error: any) {
      message.value = error.response?.data?.message || t('common.updateFailed')
      throw error
    } finally {
      isSaving.value = false
    }
  }

  async function changePassword(data: any) {
    isSaving.value = true
    message.value = ''
    try {
      await httpClient.put('/frontend/profile/password', {
        current_password: data.current_password,
        password: data.new_password,
        password_confirmation: data.confirm_password || data.password_confirmation
      })
      message.value = t('auth.passwordChangeSuccess')
      return true
    } catch (error: any) {
      message.value = error.response?.data?.message || t('auth.passwordChangeFailed')
      throw error
    } finally {
      isSaving.value = false
    }
  }

  async function updateAddress(data: any) {
    isSaving.value = true
    message.value = ''
    try {
      await httpClient.put('/frontend/profile/address', data)
      message.value = t('common.updateAddressSuccess')
      // Optionally re-fetch user to get updated address info if stored there
      await fetchProfile()
      return true
    } catch (error: any) {
      message.value = error.response?.data?.message || t('common.updateFailed')
      throw error
    } finally {
      isSaving.value = false
    }
  }

  return {
    user,
    isEditing,
    isSaving,
    isLoading,
    message,
    startEdit,
    cancelEdit,
    fetchProfile,
    updateProfile,
    changePassword,
    updateAddress
  }
}
