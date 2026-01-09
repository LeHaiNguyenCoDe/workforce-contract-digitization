<script setup lang="ts">
/**
 * Guest Info Form Component
 * Collects guest name and contact before starting chat
 */
import { ref, computed } from 'vue'
import { useLandingChatStore } from '../stores/landingChatStore'

const emit = defineEmits<{
  sessionStarted: []
}>()

const chatStore = useLandingChatStore()

const name = ref('')
const contact = ref('')
const contactType = ref<'email' | 'phone'>('email')
const isSubmitting = ref(false)
const error = ref('')

const isValid = computed(() => {
  if (!name.value.trim()) return false
  if (!contact.value.trim()) return false
  
  if (contactType.value === 'email') {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return emailRegex.test(contact.value)
  } else {
    const phoneRegex = /^[0-9]{9,11}$/
    return phoneRegex.test(contact.value.replace(/\s/g, ''))
  }
})

async function handleSubmit() {
  if (!isValid.value || isSubmitting.value) return
  
  isSubmitting.value = true
  error.value = ''
  
  try {
    await chatStore.startSession({
      name: name.value.trim(),
      contact: contact.value.trim(),
      contactType: contactType.value
    })
    emit('sessionStarted')
  } catch (e: any) {
    error.value = e.message || 'Có lỗi xảy ra. Vui lòng thử lại.'
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <form class="guest-form" @submit.prevent="handleSubmit">
    <div class="form-group">
      <label for="guest-name">Họ và tên <span class="required">*</span></label>
      <input
        id="guest-name"
        v-model="name"
        type="text"
        placeholder="Nhập họ tên của bạn"
        :disabled="isSubmitting"
        required
      />
    </div>
    
    <div class="form-group">
      <label>Thông tin liên hệ <span class="required">*</span></label>
      <div class="contact-type-toggle">
        <button 
          type="button"
          :class="{ active: contactType === 'email' }"
          @click="contactType = 'email'"
        >
          Email
        </button>
        <button 
          type="button"
          :class="{ active: contactType === 'phone' }"
          @click="contactType = 'phone'"
        >
          Số điện thoại
        </button>
      </div>
      <input
        v-model="contact"
        :type="contactType === 'email' ? 'email' : 'tel'"
        :placeholder="contactType === 'email' ? 'example@email.com' : '0901234567'"
        :disabled="isSubmitting"
        required
      />
    </div>
    
    <p v-if="error" class="error">{{ error }}</p>
    
    <button 
      type="submit" 
      class="submit-btn"
      :disabled="!isValid || isSubmitting"
    >
      <span v-if="isSubmitting">Đang xử lý...</span>
      <span v-else>Bắt đầu chat</span>
    </button>
  </form>
</template>
