import { ref } from 'vue'

const isSearchOpen = ref(false)

export const useFoodieSearch = () => {
  const openSearch = () => {
    isSearchOpen.value = true
  }

  const closeSearch = () => {
    isSearchOpen.value = false
  }

  const toggleSearch = () => {
    isSearchOpen.value = !isSearchOpen.value
  }

  return {
    isSearchOpen,
    openSearch,
    closeSearch,
    toggleSearch
  }
}
