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

<style scoped lang="scss">
.guest-form {
  padding: 4px;
}

.form-group {
  margin-bottom: 16px;
  
  label {
    display: block;
    font-size: 0.85rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 6px;
    
    .required {
      color: #ef4444;
    }
  }
  
  input {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #d1d5db;
    border-radius: 10px;
    font-size: 0.95rem;
    transition: all 0.2s ease;
    background: #fdfdfd;
    color: #0c0a09; // Strong black for maximum contrast
    
    &:focus {
      outline: none;
      background: white;
      border-color: #7c3aed;
      box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.1);
    }
    
    &::placeholder {
      color: #6b7280; // Darker placeholder for legibility
    }
    
    &:disabled {
      background: #f3f4f6;
      cursor: not-allowed;
    }
  }
}

.contact-type-toggle {
  display: flex;
  gap: 8px;
  margin-bottom: 8px;
  
  button {
    flex: 1;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: white;
    font-size: 0.85rem;
    font-weight: 500;
    color: #4b5563;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    
    &:hover {
      border-color: #7c3aed;
      color: #7c3aed;
      background: #f5f3ff;
    }
    
    &.active {
      background: #7c3aed;
      color: white;
      border-color: #7c3aed;
      box-shadow: 0 2px 6px rgba(124, 58, 237, 0.3);
    }
  }
}

.error {
  color: #ef4444;
  font-size: 0.85rem;
  margin: -8px 0 12px;
}

.submit-btn {
  width: 100%;
  padding: 12px;
  border: none;
  border-radius: 10px;
  background: linear-gradient(135deg, #6d28d9 0%, #9333ea 100%); // Darker, more vibrant purple
  color: white;
  font-size: 1rem; // Slightly larger
  font-weight: 700; // Bolder
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 4px 10px rgba(109, 40, 217, 0.3);
  
  &:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(109, 40, 217, 0.4);
    background: #5b21b6;
  }
  
  &:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }
}
</style>
