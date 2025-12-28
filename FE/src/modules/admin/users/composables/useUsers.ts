/**
 * Composable for Users
 * Provides reusable logic for user management
 */

import { ref, computed } from 'vue'

export function useUsers() {
  const store = useUserStore()

  // Local state
  const searchQuery = ref('')

  // Computed
  const filteredUsers = computed(() => {
    let result = store.users
    if (searchQuery.value) {
      const query = searchQuery.value.toLowerCase()
      result = result.filter(
        u =>
          u.name.toLowerCase().includes(query) ||
          u.email.toLowerCase().includes(query)
      )
    }
    return result
  })

  // Methods
  function formatDate(date: string) {
    return new Date(date).toLocaleDateString('vi-VN')
  }

  function setSearchQuery(value: string) {
    searchQuery.value = value
    if (store.currentPage !== 1) {
      store.setPage(1)
    }
  }

  function changePage(page: number) {
    store.setPage(page)
    store.fetchUsers({ search: searchQuery.value })
  }

  return {
    // State
    searchQuery,
    // Computed
    filteredUsers,
    // Methods
    formatDate,
    setSearchQuery,
    changePage
  }
}

