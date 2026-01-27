<script setup lang="ts">
import { ref, computed } from 'vue'
import flatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'

interface Category {
  id: number
  name: string
}

const props = defineProps<{
  categories: Category[]
  errors?: Record<string, string>
  readonly?: boolean
}>()

const isActive = defineModel<boolean>('isActive', { default: false })
const visibility = defineModel<'public' | 'private'>('visibility', { default: 'public' })
const publishedAt = defineModel<string>('publishedAt', { default: '' })
const categoryId = defineModel<string>('categoryId', { default: '' })
const tags = defineModel<string[]>('tags', { default: () => [] })
const shortDescription = defineModel<string>('shortDescription', { default: '' })

const tagInput = ref('')

const addTag = () => {
  if (props.readonly) return
  const tag = tagInput.value.trim()
  if (tag && !tags.value?.includes(tag)) {
    if (!tags.value) tags.value = []
    tags.value.push(tag)
    tagInput.value = ''
  }
}

const removeTag = (index: number) => {
  if (props.readonly) return
  tags.value?.splice(index, 1)
}
</script>

<template>
  <div>
    <!-- Publish -->
    <BCard no-body class="border-0 shadow-sm mb-4">
    <BCardHeader class="bg-transparent border-bottom py-3">
      <h5 class="card-title mb-0">Xuất bản</h5>
    </BCardHeader>
    <BCardBody class="p-4">
      <div class="mb-3">
        <label class="form-label fw-semibold mb-2">Trạng thái</label>
        <select class="form-select" v-model="isActive" :disabled="readonly">
          <option :value="true">Published</option>
          <option :value="false">Draft</option>
        </select>
      </div>
      <div>
        <label class="form-label fw-semibold mb-2">Hiển thị</label>
        <select class="form-select" v-model="visibility" :disabled="readonly">
          <option value="public">Công khai</option>
          <option value="private">Riêng tư</option>
        </select>
      </div>
    </BCardBody>
  </BCard>

  <!-- Publish Schedule -->
  <BCard no-body class="border-0 shadow-sm mb-4">
    <BCardHeader class="bg-transparent border-bottom py-3">
      <h5 class="card-title mb-0">Lịch xuất bản</h5>
    </BCardHeader>
    <BCardBody class="p-4">
      <div>
        <label class="form-label fw-semibold mb-2">Ngày & Giờ</label>
        <flatPickr v-model="publishedAt" class="form-control" placeholder="Chọn ngày xuất bản" :config="{enableTime: true, dateFormat: 'Y-m-d H:i'}" :disabled="readonly" />
      </div>
    </BCardBody>
  </BCard>

  <!-- Categories -->
  <BCard no-body class="border-0 shadow-sm mb-4">
    <BCardHeader class="bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0">Danh mục sản phẩm</h5>
      <BLink v-if="!readonly" href="javascript:void(0);" class="small text-decoration-underline">Thêm mới</BLink>
    </BCardHeader>
    <BCardBody class="p-4">
      <select class="form-select" :class="{'is-invalid': errors?.category_id}" v-model="categoryId" :disabled="readonly">
        <option value="">Chọn danh mục</option>
        <option v-for="cat in categories" :key="cat.id" :value="cat.id.toString()">
          {{ cat.name }}
        </option>
      </select>
      <div v-if="errors?.category_id" class="invalid-feedback">{{ errors.category_id }}</div>
    </BCardBody>
  </BCard>

  <!-- Tags -->
  <BCard no-body class="border-0 shadow-sm mb-4">
    <BCardHeader class="bg-transparent border-bottom py-3">
      <h5 class="card-title mb-0">Tags sản phẩm</h5>
    </BCardHeader>
    <BCardBody class="p-4">
      <div v-if="!readonly" class="input-group">
        <input type="text" class="form-control" v-model="tagInput" @keyup.enter="addTag" placeholder="Nhập tags..." />
        <BButton variant="primary" @click="addTag">Add</BButton>
      </div>
      <div class="mt-3 d-flex flex-wrap gap-1">
        <BBadge v-for="(tag, idx) in tags" :key="idx" variant="primary" class="bg-primary-subtle text-primary px-3 py-2">
          {{ tag }} <i v-if="!readonly" class="ri-close-line ms-1" style="cursor: pointer;" @click="removeTag(idx)"></i>
        </BBadge>
      </div>
    </BCardBody>
  </BCard>

  <!-- Short Description -->
  <BCard no-body class="border-0 shadow-sm mb-4">
    <BCardHeader class="bg-transparent border-bottom py-3">
      <h5 class="card-title mb-0">Mô tả ngắn</h5>
    </BCardHeader>
    <BCardBody class="p-4">
      <p class="text-muted small mb-3">Thêm mô tả ngắn để hiển thị ở trang danh sách.</p>
      <textarea class="form-control" v-model="shortDescription" rows="5" placeholder="Nhập ít nhất 100 ký tự..." :disabled="readonly"></textarea>
    </BCardBody>
  </BCard>
  </div>
</template>
