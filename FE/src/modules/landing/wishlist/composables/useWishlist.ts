/**
 * Wishlist Composable
 */

import { computed } from 'vue'
import { useWishlistStore } from '../store/store'

export function useWishlist() {
  const store = useWishlistStore()

  // Computed
  const wishlistItems = computed(() => store.items)
  const wishlistCount = computed(() => store.itemCount)
  const isEmpty = computed(() => store.items.length === 0)

  // Methods
  function isInWishlist(productId: number) {
    return store.items.some(item => item.product_id === productId)
  }

  function toggleWishlist(product: any) {
    if (isInWishlist(product.id)) {
      store.removeItem(product.id)
    } else {
      store.addItem(product)
    }
  }

  function removeFromWishlist(productId: number) {
    store.removeItem(productId)
  }

  function clearWishlist() {
    store.clearAll()
  }

  return {
    wishlistItems,
    wishlistCount,
    isEmpty,
    isInWishlist,
    toggleWishlist,
    removeFromWishlist,
    clearWishlist
  }
}
