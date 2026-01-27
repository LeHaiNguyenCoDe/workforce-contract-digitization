<script setup lang="ts">
import { ref, watch } from 'vue'

interface FAQ {
  question: string
  answer: string
}

const props = defineProps<{
  modelValue?: FAQ[] | null
  readonly?: boolean
}>()

const emit = defineEmits(['update:modelValue'])

const faqs = ref<FAQ[]>([])

watch(() => props.modelValue, (newVal) => {
  if (Array.isArray(newVal)) {
    faqs.value = newVal.map(f => ({ ...f }))
  } else {
    faqs.value = []
  }
}, { immediate: true })

const updateParent = () => {
  emit('update:modelValue', [...faqs.value])
}

const addFAQ = () => {
  faqs.value.push({ question: '', answer: '' })
  updateParent()
}

const removeFAQ = (index: number) => {
  faqs.value.splice(index, 1)
  updateParent()
}

const handleInput = () => {
  updateParent()
}
</script>

<template>
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0">Câu hỏi thường gặp (FAQ)</h5>
      <button v-if="!readonly" @click="addFAQ" class="btn btn-sm btn-outline-primary">
        <i class="ri-add-line me-1"></i> Thêm câu hỏi
      </button>
    </div>
    <div class="card-body p-4">
      <div v-if="faqs.length === 0" class="text-center py-4 text-muted">
        <i class="ri-question-answer-line fs-1 mb-2 d-block"></i>
        <p class="mb-2">Chưa có câu hỏi nào.</p>
        <small>Thêm các câu hỏi thường gặp để hiển thị trên trang sản phẩm.</small>
      </div>

      <div v-else class="space-y-3">
        <div v-for="(faq, index) in faqs" :key="index" class="border rounded p-3 mb-3 bg-light">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <label class="form-label fw-semibold mb-0">Câu hỏi {{ index + 1 }}</label>
            <button v-if="!readonly" @click="removeFAQ(index)" class="btn btn-sm btn-ghost-danger p-0">
              <i class="ri-close-line"></i>
            </button>
          </div>
          <input 
            type="text" 
            class="form-control mb-2" 
            v-model="faq.question"
            placeholder="VD: iPhone 17 Pro pin bao nhiêu?"
            :disabled="readonly"
            @input="handleInput"
          />
          <textarea 
            class="form-control" 
            v-model="faq.answer"
            rows="2"
            placeholder="Nhập câu trả lời..."
            :disabled="readonly"
            @input="handleInput"
          ></textarea>
        </div>
      </div>
    </div>
  </div>
</template>
