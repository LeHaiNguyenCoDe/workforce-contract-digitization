<script setup lang="ts">
import { computed, ref } from 'vue'
import { storeToRefs } from 'pinia'
import { useAdminCategoryStore, type Category } from '../../../store/categoryStore'

const props = defineProps<{
  editingCategory: Category | null
}>()

const emit = defineEmits<{
  save: []
  cancel: []
}>()

const modelValue = defineModel<boolean>({ default: false })

// Store
const store = useAdminCategoryStore()
const { categories, isSaving, categoryForm } = storeToRefs(store)

// Computed
const modalTitle = computed(() => props.editingCategory ? 'Chỉnh sửa danh mục' : 'Thêm danh mục')

const availableParentCategories = computed(() => {
  return categories.value.filter(c => c.id !== props.editingCategory?.id)
})

// Methods
function generateSlug(text: string): string {
  return text
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/đ/g, 'd')
    .replace(/[^a-z0-9\s-]/g, '')
    .trim()
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-')
}

function handleNameChange() {
  if (!props.editingCategory && categoryForm.value.name) {
    categoryForm.value.slug = generateSlug(categoryForm.value.name)
  }
}

function handleSave() {
  emit('save')
}

const fileInput = ref<HTMLInputElement | null>(null)

function triggerFileInput() {
  fileInput.value?.click()
}

function handleImageUpload(event: Event) {
  const file = (event.target as HTMLInputElement).files?.[0]
  if (file) {
    const reader = new FileReader()
    reader.onload = (e) => {
      categoryForm.value.image = e.target?.result as string
    }
    reader.readAsDataURL(file)
  }
}

function clearImage() {
  categoryForm.value.image = ''
  if (fileInput.value) fileInput.value.value = ''
}

function handleClose() {
  modelValue.value = false
  emit('cancel')
}
</script>

<template>
  <DModal v-model="modelValue" :title="modalTitle" size="md">
    <!-- Form Body -->
    <div class="form-group">
      <label class="form-label">Tên danh mục</label>
      <input 
        v-model="categoryForm.name" 
        @input="handleNameChange" 
        type="text" 
        class="form-control"
        placeholder="Nhập tên" 
      />
    </div>
    
    <div class="form-group">
      <label class="form-label">Slug</label>
      <input 
        v-model="categoryForm.slug" 
        type="text" 
        class="form-control" 
        placeholder="tu-dong-tao-tu-ten" 
      />
    </div>
    
    <div class="form-group">
      <label class="form-label">Danh mục cha</label>
      <select v-model="categoryForm.parent_id" class="form-select">
        <option value="">Danh mục cha</option>
        <option 
          v-for="cat in availableParentCategories" 
          :key="cat.id"
          :value="cat.id.toString()"
        >
          {{ cat.name }}
        </option>
      </select>
    </div>
    
    <div class="form-group">
      <label class="form-label">Mô tả</label>
      <textarea 
        v-model="categoryForm.description" 
        class="form-control" 
        rows="3"
        placeholder="Mô tả danh mục"
      ></textarea>
    </div>

    <!-- Image Upload -->
    <div class="form-group">
      <label class="form-label">Hình ảnh danh mục</label>
      <input 
        type="file" 
        ref="fileInput" 
        class="d-none" 
        accept="image/*" 
        @change="handleImageUpload" 
      />
      
      <div 
        @click="triggerFileInput" 
        class="border border-dashed rounded-3 p-3 bg-light text-center position-relative" 
        style="cursor: pointer; min-height: 120px;"
      >
        <div v-if="!categoryForm.image" class="py-2">
          <i class="ri-image-add-line fs-24 text-muted"></i>
          <p class="mb-0 mt-2 fs-13 text-muted">Click để tải ảnh lên</p>
        </div>
        <div v-else class="position-relative d-inline-block">
          <img 
            :src="categoryForm.image" 
            class="img-fluid rounded border" 
            style="max-height: 150px;" 
          />
          <button 
            type="button" 
            class="btn btn-danger btn-sm position-absolute top-0 end-0 m-n2 rounded-circle p-0 d-flex align-items-center justify-content-center" 
            style="width: 22px; height: 22px; right: -10px; top: -10px;"
            @click.stop="clearImage"
          >
            <i class="ri-close-line"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Visibility Toggle -->
    <div class="form-group mt-3">
      <div class="form-check form-switch">
        <input 
          v-model="categoryForm.is_active" 
          class="form-check-input" 
          type="checkbox" 
          id="categoryVisibility"
        >
        <label class="form-check-label fw-medium ms-1" for="categoryVisibility">
          Hiển thị danh mục trên trang web
        </label>
      </div>
      <small class="text-muted d-block mt-1">
        Nếu tắt, danh mục này sẽ không xuất hiện trên menu và trang chủ.
      </small>
    </div>

    <!-- Footer -->
    <template #footer>
      <button type="button" class="btn-close-modal" @click="handleClose">
        Đóng
      </button>
      <button 
        type="button" 
        class="btn-submit" 
        :disabled="isSaving" 
        @click="handleSave"
      >
        <span v-if="isSaving" class="spinner-border spinner-border-sm me-1"></span>
        {{ editingCategory ? 'Cập nhật' : 'Thêm danh mục' }}
      </button>
    </template>
  </DModal>
</template>
