/**
 * Cart Composable - Enhanced version
 * Features: debounce updates, promo codes, optimistic UI
 */

import { ref, computed, onMounted, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import httpClient from '@/plugins/api/httpClient'
import { useAuthStore } from '@/stores'

// Debounce utility
function debounce<T extends (...args: any[]) => any>(fn: T, delay: number) {
  let timeoutId: ReturnType<typeof setTimeout>
  return (...args: Parameters<T>) => {
    clearTimeout(timeoutId)
    timeoutId = setTimeout(() => fn(...args), delay)
  }
}

export interface CartItem {
  id: number
  product_id: number
  qty: number
  price: number
  subtotal: number
  product: {
    id: number
    name: string
    thumbnail?: string
    price: number
    sale_price?: number
  }
}

export interface Cart {
  id: number
  items: CartItem[]
  total: number
  subtotal: number
  discount?: number
  promo_code?: string
}

export function useCart() {
  const { t } = useI18n()
  // State
  const cart = ref<Cart | null>(null)
  const isLoading = ref(true)
  const authStore = useAuthStore()
  
  // Promo code state
  const promoCode = ref('')
  const promoError = ref<string | null>(null)
  const promoSuccess = ref<string | null>(null)
  const isApplyingPromo = ref(false)
  
  // Loading state per item
  const updatingItems = ref<Set<number>>(new Set())
  
  // Toast/notification state
  const notification = ref<{ type: 'success' | 'error'; message: string } | null>(null)

  // Computed
  const total = computed(() => cart.value?.total || 0)
  const subtotal = computed(() => cart.value?.subtotal || cart.value?.total || 0)
  const discount = computed(() => cart.value?.discount || 0)
  const isEmpty = computed(() => !cart.value?.items?.length)
  const itemCount = computed(() => cart.value?.items?.reduce((sum, item) => sum + item.qty, 0) || 0)
  const savings = computed(() => {
    if (!cart.value?.items) return 0
    return cart.value.items.reduce((sum, item) => {
      const originalPrice = item.product.price * item.qty
      const currentPrice = item.subtotal
      return sum + (originalPrice - currentPrice)
    }, 0)
  })

  // Methods
  function formatPrice(price: number | undefined | null) {
    if (price === undefined || price === null || isNaN(price)) {
      return '0 ₫'
    }
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price)
  }

  function showNotification(type: 'success' | 'error', message: string) {
    notification.value = { type, message }
    setTimeout(() => {
      notification.value = null
    }, 3000)
  }

  async function fetchCart() {
    if (!authStore.isAuthenticated) {
      cart.value = null
      isLoading.value = false
      return
    }

    isLoading.value = true
    try {
      const response = await httpClient.get('/frontend/cart')
      const data = response.data as any
      cart.value = data?.data || data
    } catch (error) {
      console.error('Failed to fetch cart:', error)
      cart.value = null
    } finally {
      isLoading.value = false
    }
  }

  // Debounced update quantity
  const debouncedUpdateApi = debounce(async (itemId: number, qty: number) => {
    try {
      await httpClient.put(`/frontend/cart/items/${itemId}`, { qty })
      await fetchCart()
      showNotification('success', t('cart.quantityUpdated'))
    } catch (error) {
      console.error('Failed to update cart item:', error)
      showNotification('error', t('cart.quantityUpdateFailed'))
      await fetchCart() // Revert on error
    } finally {
      updatingItems.value.delete(itemId)
    }
  }, 500)

  async function updateQuantity(itemId: number, qty: number) {
    if (qty < 1) return
    
    // Optimistic update
    if (cart.value?.items) {
      const item = cart.value.items.find(i => i.id === itemId)
      if (item) {
        item.qty = qty
        item.subtotal = item.price * qty
        // Recalculate total
        cart.value.total = cart.value.items.reduce((sum, i) => sum + i.subtotal, 0)
      }
    }
    
    updatingItems.value.add(itemId)
    debouncedUpdateApi(itemId, qty)
  }

  async function removeItem(itemId: number) {
    // Optimistic update - remove immediately from UI
    const removedItem = cart.value?.items?.find(i => i.id === itemId)
    if (cart.value?.items) {
      cart.value.items = cart.value.items.filter(i => i.id !== itemId)
      cart.value.total = cart.value.items.reduce((sum, i) => sum + i.subtotal, 0)
    }
    
    try {
      await httpClient.delete(`/frontend/cart/items/${itemId}`)
      authStore.decrementCartCount()
      showNotification('success', t('cart.removedFromCart'))
    } catch (error) {
      console.error('Failed to remove cart item:', error)
      showNotification('error', t('cart.removeFailed'))
      await fetchCart() // Revert on error
    }
  }

  async function clearCart() {
    if (!cart.value?.items?.length) return
    
    try {
      await httpClient.delete('/frontend/cart')
      authStore.setCartCount(0)
      cart.value = null
      showNotification('success', t('cart.cartCleared'))
    } catch (error) {
      console.error('Failed to clear cart:', error)
      showNotification('error', t('cart.clearFailed'))
    }
  }

  async function applyPromoCode(code: string) {
    if (!code.trim()) {
      promoError.value = t('cart.enterPromoRequired')
      return false
    }
    
    isApplyingPromo.value = true
    promoError.value = null
    promoSuccess.value = null
    
    try {
      await httpClient.post('/frontend/cart/promo', { code: code.trim() })
      await fetchCart()
      promoSuccess.value = t('cart.promoApplied')
      promoCode.value = code.trim()
      return true
    } catch (error: any) {
      promoError.value = error.response?.data?.message || t('cart.promoInvalid')
      return false
    } finally {
      isApplyingPromo.value = false
    }
  }

  async function removePromoCode() {
    try {
      await httpClient.delete('/frontend/cart/promo')
      await fetchCart()
      promoCode.value = ''
      promoSuccess.value = null
      showNotification('success', t('cart.promoRemoved'))
    } catch (error) {
      console.error('Failed to remove promo:', error)
    }
  }

  function isItemUpdating(itemId: number) {
    return updatingItems.value.has(itemId)
  }

  onMounted(fetchCart)

  return {
    // State
    cart,
    isLoading,
    promoCode,
    promoError,
    promoSuccess,
    isApplyingPromo,
    notification,
    
    // Computed
    total,
    subtotal,
    discount,
    isEmpty,
    itemCount,
    savings,
    
    // Methods
    formatPrice,
    fetchCart,
    updateQuantity,
    removeItem,
    clearCart,
    applyPromoCode,
    removePromoCode,
    isItemUpdating
  }
}
