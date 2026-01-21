<script setup lang="ts">
import { ref, watch } from 'vue'

interface Variant {
  id?: number
  variant_type: 'storage' | 'color'
  label: string
  price_adjustment: number
  stock: number
  is_default: boolean
  color_code?: string
}

const props = defineProps<{
  modelValue?: Variant[] | null
  readonly?: boolean
}>()

const emit = defineEmits(['update:modelValue'])

const variants = ref<Variant[]>([])

// Storage presets
const storagePresets = ['64GB', '128GB', '256GB', '512GB', '1TB']

// Color presets
const colorPresets = [
  { label: 'Đen', code: '#000000' },
  { label: 'Trắng', code: '#FFFFFF' },
  { label: 'Xanh', code: '#0066CC' },
  { label: 'Tím', code: '#9966CC' },
  { label: 'Vàng', code: '#FFD700' },
  { label: 'Titan', code: '#8B8589' }
]

watch(() => props.modelValue, (newVal) => {
  if (Array.isArray(newVal)) {
    variants.value = newVal.map(v => ({ ...v }))
  } else {
    variants.value = []
  }
}, { immediate: true })

const updateParent = () => {
  emit('update:modelValue', [...variants.value])
}

const addVariant = (type: 'storage' | 'color') => {
  variants.value.push({
    variant_type: type,
    label: '',
    price_adjustment: 0,
    stock: 0,
    is_default: variants.value.length === 0,
    color_code: type === 'color' ? '#000000' : undefined
  })
  updateParent()
}

const removeVariant = (index: number) => {
  variants.value.splice(index, 1)
  updateParent()
}

const handleInput = () => {
  updateParent()
}

const setDefault = (index: number) => {
  variants.value.forEach((v, i) => {
    v.is_default = i === index
  })
  updateParent()
}
</script>

<template>
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0">Biến thể sản phẩm</h5>
      <div v-if="!readonly" class="btn-group btn-group-sm">
        <button @click="addVariant('storage')" class="btn btn-outline-primary">
          <i class="ri-hard-drive-2-line me-1"></i> Thêm Dung lượng
        </button>
        <button @click="addVariant('color')" class="btn btn-outline-success">
          <i class="ri-palette-line me-1"></i> Thêm Màu sắc
        </button>
      </div>
    </div>
    <div class="card-body p-4">
      <div v-if="variants.length === 0" class="text-center py-4 text-muted">
        <i class="ri-stack-line fs-1 mb-2 d-block"></i>
        <p class="mb-2">Chưa có biến thể nào.</p>
        <small>Thêm biến thể dung lượng (128GB, 256GB...) hoặc màu sắc để hiển thị trên trang sản phẩm.</small>
      </div>

      <div v-else class="table-responsive">
        <table class="table table-bordered mb-0">
          <thead class="table-light">
            <tr>
              <th style="width: 100px;">Loại</th>
              <th>Tên biến thể</th>
              <th style="width: 130px;">Điều chỉnh giá</th>
              <th style="width: 100px;">Tồn kho</th>
              <th style="width: 80px;">Mặc định</th>
              <th v-if="!readonly" style="width: 60px;"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(variant, index) in variants" :key="index">
              <td>
                <span :class="variant.variant_type === 'storage' ? 'badge bg-primary' : 'badge bg-success'">
                  {{ variant.variant_type === 'storage' ? 'Dung lượng' : 'Màu sắc' }}
                </span>
              </td>
              <td>
                <div class="d-flex gap-2 align-items-center">
                  <input 
                    v-if="variant.variant_type === 'color'"
                    type="color" 
                    v-model="variant.color_code"
                    class="form-control form-control-color"
                    style="width: 40px; height: 32px;"
                    :disabled="readonly"
                    @change="handleInput"
                  />
                  <input 
                    type="text" 
                    class="form-control form-control-sm" 
                    v-model="variant.label"
                    :list="variant.variant_type === 'storage' ? 'storageList' : 'colorList'"
                    :placeholder="variant.variant_type === 'storage' ? '256GB' : 'Titan Blue'"
                    :disabled="readonly"
                    @input="handleInput"
                  />
                </div>
              </td>
              <td>
                <div class="input-group input-group-sm">
                  <span class="input-group-text">₫</span>
                  <input 
                    type="number" 
                    class="form-control" 
                    v-model.number="variant.price_adjustment"
                    placeholder="0"
                    :disabled="readonly"
                    @input="handleInput"
                  />
                </div>
              </td>
              <td>
                <input 
                  type="number" 
                  class="form-control form-control-sm" 
                  v-model.number="variant.stock"
                  placeholder="0"
                  :disabled="readonly"
                  @input="handleInput"
                />
              </td>
              <td class="text-center">
                <input 
                  type="radio" 
                  class="form-check-input" 
                  :checked="variant.is_default"
                  :disabled="readonly"
                  @change="setDefault(index)"
                />
              </td>
              <td v-if="!readonly" class="text-center">
                <button @click="removeVariant(index)" class="btn btn-sm btn-ghost-danger">
                  <i class="ri-delete-bin-line"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <datalist id="storageList">
        <option v-for="s in storagePresets" :key="s" :value="s"></option>
      </datalist>
      <datalist id="colorList">
        <option v-for="c in colorPresets" :key="c.label" :value="c.label"></option>
      </datalist>
    </div>
  </div>
</template>
