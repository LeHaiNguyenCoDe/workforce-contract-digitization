/**
 * Landing Cart Module Store
 */

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import httpClient from '@/plugins/api/httpClient'
import type { Cart, CartItem, AddToCartPayload, UpdateCartPayload } from './types'
import { calculateCartTotals } from './helpers'

export const useCartStore = defineStore('landing-cart', () => {
  // State
  const items = ref<CartItem[]>([])
  const isLoading = ref(false)
  const shipping = ref(0)
  const discount = ref(0)

  // Getters
  const cart = computed<Cart>(() => calculateCartTotals(items.value, shipping.value, discount.value))
  const itemCount = computed(() => cart.value.item_count)
  const isEmpty = computed(() => items.value.length === 0)
  const subtotal = computed(() => cart.value.subtotal)
  const total = computed(() => cart.value.total)

  // Actions
  async function fetchCart() {
    isLoading.value = true
    try {
      const response = await httpClient.get<any>('/cart')
      const data = response.data as any
      
      if (data?.data?.items) {
        items.value = data.data.items
        shipping.value = data.data.shipping || 0
        discount.value = data.data.discount || 0
      } else if (Array.isArray(data?.data)) {
        items.value = data.data
      }
    } catch (error) {
      console.error('Failed to fetch cart:', error)
    } finally {
      isLoading.value = false
    }
  }

  async function addToCart(payload: AddToCartPayload): Promise<boolean> {
    isLoading.value = true
    try {
      await httpClient.post('/cart', payload)
      await fetchCart()
      return true
    } catch (error) {
      console.error('Failed to add to cart:', error)
      return false
    } finally {
      isLoading.value = false
    }
  }

  async function updateCartItem(itemId: number, payload: UpdateCartPayload): Promise<boolean> {
    try {
      await httpClient.put(`/cart/${itemId}`, payload)
      await fetchCart()
      return true
    } catch (error) {
      console.error('Failed to update cart item:', error)
      return false
    }
  }

  async function removeFromCart(itemId: number): Promise<boolean> {
    try {
      await httpClient.delete(`/cart/${itemId}`)
      items.value = items.value.filter(item => item.id !== itemId)
      return true
    } catch (error) {
      console.error('Failed to remove from cart:', error)
      return false
    }
  }

  async function clearCart(): Promise<boolean> {
    try {
      await httpClient.delete('/cart')
      items.value = []
      return true
    } catch (error) {
      console.error('Failed to clear cart:', error)
      return false
    }
  }

  function reset() {
    items.value = []
    shipping.value = 0
    discount.value = 0
  }

  return {
    // State
    items,
    isLoading,
    shipping,
    discount,
    // Getters
    cart,
    itemCount,
    isEmpty,
    subtotal,
    total,
    // Actions
    fetchCart,
    addToCart,
    updateCartItem,
    removeFromCart,
    clearCart,
    reset
  }
})
