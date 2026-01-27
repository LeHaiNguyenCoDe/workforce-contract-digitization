/**
 * Checkout Composable - Enhanced version
 * Features: multi-step wizard, form persistence, validation per step
 */

import { ref, computed, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores'
import httpClient from '@/plugins/api/httpClient'

const STORAGE_KEY = 'checkout_form_data'

export interface ShippingAddress {
  id: number
  full_name: string
  phone: string
  address_line: string
  province?: string
  district?: string
  ward?: string
  is_default?: boolean
}

export interface CheckoutForm {
  full_name: string
  phone: string
  email: string
  address_line: string
  province: string
  district: string
  ward: string
  payment_method: string
  note: string
}

export interface PaymentMethod {
  id: string
  icon: string
  name: string
  desc: string
  disabled?: boolean
}

export function useCheckout() {
  const router = useRouter()
  const authStore = useAuthStore()
  
  // Multi-step state
  const currentStep = ref(1)
  const totalSteps = 3
  
  // Form state
  const form = ref<CheckoutForm>({
    full_name: '',
    phone: '',
    email: '',
    address_line: '',
    province: '',
    district: '',
    ward: '',
    payment_method: 'cod',
    note: ''
  })
  
  // Validation errors per field
  const errors = ref<Record<string, string>>({})
  
  // Saved addresses
  const savedAddresses = ref<ShippingAddress[]>([])
  const selectedAddressId = ref<number | null>(null)
  const isLoadingAddresses = ref(false)
  
  // Submit state
  const isSubmitting = ref(false)
  const submitError = ref<string | null>(null)
  
  // Payment methods
  const paymentMethods: PaymentMethod[] = [
    { id: 'cod', icon: '💵', name: 'Thanh toán khi nhận hàng (COD)', desc: 'Trả tiền mặt khi nhận hàng' },
    { id: 'bank_transfer', icon: '🏦', name: 'Chuyển khoản ngân hàng', desc: 'Chuyển khoản qua tài khoản ngân hàng' },
    { id: 'momo', icon: '📱', name: 'Ví MoMo', desc: 'Thanh toán qua ví điện tử MoMo' },
    { id: 'vnpay', icon: '💳', name: 'VNPay', desc: 'Thanh toán qua cổng VNPay' },
    { id: 'zalopay', icon: '🔵', name: 'ZaloPay', desc: 'Thanh toán qua ví ZaloPay' }
  ]
  
  // Step computed properties
  const stepProgress = computed(() => (currentStep.value / totalSteps) * 100)
  const isFirstStep = computed(() => currentStep.value === 1)
  const isLastStep = computed(() => currentStep.value === totalSteps)
  
  const stepTitles = [
    { step: 1, title: 'Địa chỉ giao hàng', icon: '📍' },
    { step: 2, title: 'Thanh toán', icon: '💳' },
    { step: 3, title: 'Xác nhận', icon: '✅' }
  ]
  
  // Validation functions
  function validateStep1(): boolean {
    errors.value = {}
    
    if (!form.value.full_name.trim()) {
      errors.value.full_name = 'Vui lòng nhập họ tên'
    }
    
    if (!form.value.phone.trim()) {
      errors.value.phone = 'Vui lòng nhập số điện thoại'
    } else if (!/^(0|\+84)[3-9][0-9]{8}$/.test(form.value.phone.replace(/\s/g, ''))) {
      errors.value.phone = 'Số điện thoại không hợp lệ'
    }
    
    if (form.value.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) {
      errors.value.email = 'Email không hợp lệ'
    }
    
    if (!form.value.address_line.trim()) {
      errors.value.address_line = 'Vui lòng nhập địa chỉ'
    }
    
    return Object.keys(errors.value).length === 0
  }
  
  function validateStep2(): boolean {
    errors.value = {}
    
    if (!form.value.payment_method) {
      errors.value.payment_method = 'Vui lòng chọn phương thức thanh toán'
    }
    
    return Object.keys(errors.value).length === 0
  }
  
  function validateCurrentStep(): boolean {
    switch (currentStep.value) {
      case 1:
        return validateStep1()
      case 2:
        return validateStep2()
      default:
        return true
    }
  }
  
  // Navigation functions
  function nextStep() {
    if (validateCurrentStep() && currentStep.value < totalSteps) {
      currentStep.value++
      saveFormToStorage()
    }
  }
  
  function prevStep() {
    if (currentStep.value > 1) {
      currentStep.value--
    }
  }
  
  function goToStep(step: number) {
    // Only allow going back or to current step
    if (step <= currentStep.value && step >= 1) {
      currentStep.value = step
    }
  }
  
  // Storage functions
  function saveFormToStorage() {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify({
        form: form.value,
        step: currentStep.value
      }))
    } catch (e) {
      console.warn('Failed to save form to storage')
    }
  }
  
  function loadFormFromStorage() {
    try {
      const saved = localStorage.getItem(STORAGE_KEY)
      if (saved) {
        const parsed = JSON.parse(saved)
        if (parsed.form) {
          form.value = { ...form.value, ...parsed.form }
        }
      }
    } catch (e) {
      console.warn('Failed to load form from storage')
    }
  }
  
  function clearFormStorage() {
    try {
      localStorage.removeItem(STORAGE_KEY)
    } catch (e) {
      // Ignore
    }
  }
  
  // Fetch saved addresses
  async function fetchSavedAddresses() {
    if (!authStore.isAuthenticated) return
    
    isLoadingAddresses.value = true
    try {
      const response = await httpClient.get('/addresses')
      const data = response.data as any
      savedAddresses.value = data?.data || data || []
      
      // Auto-select default address
      const defaultAddr = savedAddresses.value.find(a => a.is_default)
      if (defaultAddr) {
        selectAddress(defaultAddr)
      }
    } catch (error) {
      console.error('Failed to fetch addresses:', error)
    } finally {
      isLoadingAddresses.value = false
    }
  }
  
  function selectAddress(address: ShippingAddress) {
    selectedAddressId.value = address.id
    form.value.full_name = address.full_name
    form.value.phone = address.phone
    form.value.address_line = address.address_line
    form.value.province = address.province || ''
    form.value.district = address.district || ''
    form.value.ward = address.ward || ''
  }
  
  // Create order
  async function createOrder() {
    if (!validateStep1() || !validateStep2()) {
      return
    }
    
    isSubmitting.value = true
    submitError.value = null
    
    try {
      const response = await httpClient.post('/orders', form.value)
      const order = response.data as any
      
      authStore.setCartCount(0)
      clearFormStorage()
      
      const orderId = order.id || order.data?.id
      router.push(`/orders/${orderId}/success`)
      return order
    } catch (err: any) {
      console.error('Order creation failed:', err)
      submitError.value = err.response?.data?.message || 'Không thể tạo đơn hàng. Vui lòng thử lại.'
      throw err
    } finally {
      isSubmitting.value = false
    }
  }
  
  // Watch form changes and auto-save
  watch(form, () => {
    saveFormToStorage()
  }, { deep: true })
  
  // Initialize
  onMounted(() => {
    loadFormFromStorage()
    fetchSavedAddresses()
    
    // Pre-fill from user profile
    if (authStore.user) {
      if (!form.value.full_name) form.value.full_name = authStore.user.name || ''
      if (!form.value.email) form.value.email = authStore.user.email || ''
    }
  })
  
  return {
    // Step state
    currentStep,
    totalSteps,
    stepProgress,
    isFirstStep,
    isLastStep,
    stepTitles,
    
    // Form
    form,
    errors,
    
    // Addresses
    savedAddresses,
    selectedAddressId,
    isLoadingAddresses,
    
    // Payment
    paymentMethods,
    
    // Submit
    isSubmitting,
    submitError,
    
    // Methods
    nextStep,
    prevStep,
    goToStep,
    validateCurrentStep,
    selectAddress,
    fetchSavedAddresses,
    createOrder,
    clearFormStorage
  }
}
