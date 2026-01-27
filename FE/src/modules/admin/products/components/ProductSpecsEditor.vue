<script setup lang="ts">
import { ref, watch } from 'vue'

const props = defineProps<{
  modelValue?: Record<string, string> | null
  readonly?: boolean
}>()

const emit = defineEmits(['update:modelValue'])

// Internal state for the list of specs
const specsList = ref<{ key: string; value: string }[]>([])

// Common keys for autocomplete suggestions
const commonKeys = [
  'Kích thước màn hình', 
  'Công nghệ màn hình', 
  'Camera sau', 
  'Camera trước',
  'Chipset', 
  'Dung lượng RAM', 
  'Bộ nhớ trong', 
  'Pin', 
  'Thẻ SIM', 
  'Hệ điều hành', 
  'Độ phân giải màn hình',
  'Tính năng màn hình',
  'Tần số quét',
  'Loại CPU',
  'Công nghệ NFC'
]

// Initialize from props
watch(() => props.modelValue, (newVal) => {
  if (newVal && typeof newVal === 'object') {
    specsList.value = Object.entries(newVal).map(([key, value]) => ({
      key: key.replace(/_/g, ' '),
      value: String(value)
    }))
  } else {
    specsList.value = []
  }
}, { immediate: true })

// Update parent when list changes
const updateParent = () => {
  const specsObj: Record<string, string> = {}
  specsList.value.forEach(item => {
    if (item.key.trim()) {
      specsObj[item.key.trim()] = item.value
    }
  })
  emit('update:modelValue', specsObj)
}

const addSpec = () => {
  specsList.value.push({ key: '', value: '' })
}

const removeSpec = (index: number) => {
  specsList.value.splice(index, 1)
  updateParent()
}

const handleInput = () => {
  updateParent()
}
</script>

<template>
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent border-bottom px-4 py-3">
      <h5 class="card-title mb-0">Technical Specifications</h5>
    </div>
    <div class="card-body p-4">
      <div v-if="specsList.length === 0" class="text-center py-3 text-muted">
        <p class="mb-2">No specifications added yet.</p>
        <button v-if="!readonly" @click="addSpec" class="btn btn-sm btn-outline-primary">
          <i class="ri-add-line align-bottom"></i> Add Specification
        </button>
      </div>

      <div v-else class="space-y-3">
        <div v-for="(spec, index) in specsList" :key="index" class="row g-2 mb-2 align-items-center">
          <div class="col-md-5">
            <input 
              type="text" 
              class="form-control" 
              v-model="spec.key" 
              list="commonKeysList"
              placeholder="Feature (e.g., Screen Size)"
              :disabled="readonly"
              @input="handleInput"
            />
          </div>
          <div class="col-md-6">
             <input 
              type="text" 
              class="form-control" 
              v-model="spec.value" 
              placeholder="Value (e.g., 6.7 inch)" 
              :disabled="readonly"
              @input="handleInput"
            />
          </div>
          <div class="col-md-1 text-end" v-if="!readonly">
            <button @click="removeSpec(index)" class="btn btn-icon btn-sm btn-ghost-danger">
              <i class="ri-delete-bin-line"></i>
            </button>
          </div>
        </div>

        <div v-if="!readonly" class="mt-3">
           <button @click="addSpec" class="btn btn-sm btn-light text-primary">
            <i class="ri-add-circle-line align-middle me-1"></i> Add Another Row
          </button>
        </div>
      </div>

      <datalist id="commonKeysList">
        <option v-for="key in commonKeys" :key="key" :value="key"></option>
      </datalist>
    </div>
  </div>
</template>
