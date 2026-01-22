/**
 * Landing Wishlist Module Store
 */

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import httpClient from '@/plugins/api/httpClient'
import type { WishlistItem } from './types'

export const useWishlistStore = defineStore('landing-wishlist', () => {
  // State
  const items = ref<WishlistItem[]>([])
  const isLoading = ref(false)

  // Getters
  const itemCount = computed(() => items.value.length)
  const isEmpty = computed(() => items.value.length === 0)
  const productIds = computed(() => items.value.map(i => i.product_id))

  // Actions
  async function fetchWishlist() {
    isLoading.value = true
    try {
      const response = await httpClient.get<any>('/wishlist')
      const data = response.data as any
      items.value = Array.isArray(data?.data) ? data.data : []
    } catch (error) {
      console.error('Failed to fetch wishlist:', error)
    } finally {
      isLoading.value = false
    }
  }

  async function addToWishlist(productId: number): Promise<boolean> {
    try {
      await httpClient.post('/wishlist', { product_id: productId })
      await fetchWishlist()
      return true
    } catch (error) {
      console.error('Failed to add to wishlist:', error)
      return false
    }
  }

  async function removeFromWishlist(productId: number): Promise<boolean> {
    try {
      await httpClient.delete(`/wishlist/${productId}`)
      items.value = items.value.filter(i => i.product_id !== productId)
      return true
    } catch (error) {
      console.error('Failed to remove from wishlist:', error)
      return false
    }
  }

  function isInWishlist(productId: number): boolean {
    return productIds.value.includes(productId)
  }

  async function toggleWishlist(productId: number): Promise<boolean> {
    if (isInWishlist(productId)) {
      return removeFromWishlist(productId)
    }
    return addToWishlist(productId)
  }

  function reset() {
    items.value = []
  }

  return {
    items,
    isLoading,
    itemCount,
    isEmpty,
    productIds,
    fetchWishlist,
    addToWishlist,
    removeFromWishlist,
    isInWishlist,
    toggleWishlist,
    reset
  }
})
