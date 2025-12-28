/**
 * Landing Categories Composable
 */

import { ref, computed, onMounted } from 'vue'
import { useLandingCategoryStore } from '../store/store'
import type { Category } from '@/shared/types'

export function useCategories() {
  const store = useLandingCategoryStore()

  // Local state
  const searchQuery = ref('')

  // Computed
  const filteredCategories = computed(() => {
    if (!searchQuery.value) return store.categories
    const query = searchQuery.value.toLowerCase()
    return store.categories.filter(c => c.name.toLowerCase().includes(query))
  })

  // Methods
  function setSearch(query: string) {
    searchQuery.value = query
  }

  async function loadCategories() {
    await store.fetchCategories()
  }

  onMounted(loadCategories)

  return {
    searchQuery,
    categories: computed(() => store.categories),
    isLoading: computed(() => store.isLoading),
    filteredCategories,
    setSearch,
    loadCategories
  }
}
