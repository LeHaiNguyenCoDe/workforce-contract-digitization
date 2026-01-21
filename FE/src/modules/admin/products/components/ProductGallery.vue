<script setup lang="ts">
import { ref } from 'vue'

const props = defineProps<{
  thumbnail?: string
  images?: string[]
  readonly?: boolean
}>()

const emit = defineEmits([
  'update:thumbnail',
  'update:images',
  'remove-gallery-image'
])

const mainImageInput = ref<HTMLInputElement | null>(null)
const galleryInput = ref<HTMLInputElement | null>(null)

const handleMainImageUpload = (event: Event) => {
  const file = (event.target as HTMLInputElement).files?.[0]
  if (file) {
    const reader = new FileReader()
    reader.onload = (e) => {
      emit('update:thumbnail', e.target?.result as string)
    }
    reader.readAsDataURL(file)
  }
}

const handleGalleryUpload = async (event: Event) => {
  const files = (event.target as HTMLInputElement).files
  if (files && files.length > 0) {
    const readers: Promise<string>[] = []
    
    Array.from(files).forEach(file => {
      readers.push(new Promise((resolve) => {
        const reader = new FileReader()
        reader.onload = (e) => resolve(e.target?.result as string)
        reader.readAsDataURL(file)
      }))
    })

    try {
      const newBase64Images = await Promise.all(readers)
      const currentImages = props.images || []
      emit('update:images', [...currentImages, ...newBase64Images])
      
      // Reset input value to allow selecting the same files again
      if (galleryInput.value) {
        galleryInput.value.value = ''
      }
    } catch (error) {
      console.error('Error reading gallery images:', error)
    }
  }
}

const removeGalleryImage = (index: number) => {
  emit('remove-gallery-image', index)
}

const clearThumbnail = () => {
  emit('update:thumbnail', '')
}
</script>

<template>
  <BCard no-body class="border-0 shadow-sm mb-4">
    <BCardHeader class="bg-transparent border-bottom py-3">
      <h5 class="card-title mb-0">Thư viện sản phẩm</h5>
    </BCardHeader>
    <BCardBody class="p-4">
      <div class="mb-4">
        <label class="form-label fs-14 mb-2">Hình ảnh sản phẩm</label>
        <p class="text-muted small mb-3">Thêm hình ảnh chính cho sản phẩm.</p>
        
        <input v-if="!readonly" type="file" ref="mainImageInput" class="d-none" accept="image/*" @change="handleMainImageUpload" />
        <div v-if="!readonly || !thumbnail" @click="!readonly ? mainImageInput?.click() : null" class="d-flex align-items-center justify-content-center border border-dashed rounded-3 p-4 bg-light position-relative" :class="{'cursor-pointer': !readonly}" style="min-height: 150px;">
           <div v-if="!thumbnail" class="text-center">
             <div class="avatar-md mx-auto mb-3">
               <div class="avatar-title bg-light text-secondary rounded-circle fs-24">
                 <i class="ri-upload-cloud-2-line"></i>
               </div>
             </div>
             <h5 v-if="!readonly" class="fs-15 mb-0">Thả ảnh vào đây hoặc click để tải lên.</h5>
             <h5 v-else class="fs-15 mb-0 text-muted">Chưa có ảnh đại diện</h5>
           </div>
           <div v-else class="position-relative w-100 h-100 d-flex justify-content-center">
              <img :src="thumbnail" class="img-fluid rounded" style="max-height: 200px" />
              <BButton v-if="!readonly" variant="danger" size="sm" class="position-absolute top-0 end-0 m-2" @click.stop="clearThumbnail">
                <i class="ri-close-line"></i>
              </BButton>
           </div>
        </div>
        <div v-else class="text-center border rounded p-4">
             <img :src="thumbnail" class="img-fluid rounded" style="max-height: 200px" />
        </div>
      </div>

      <div>
        <label class="form-label fs-14 mb-2">Bộ sưu tập hình ảnh</label>
        <p class="text-muted small mb-3">Thêm nhiều hình ảnh khác cho sản phẩm.</p>
        <div v-if="!readonly">
          <input type="file" ref="galleryInput" class="d-none" accept="image/*" multiple @change="handleGalleryUpload" />
          <div @click="galleryInput?.click()" class="d-flex align-items-center justify-content-center border border-dashed rounded-3 p-4 mb-3" style="cursor: pointer; min-height: 150px;">
             <div class="text-center">
               <div class="avatar-md mx-auto mb-3">
                 <div class="avatar-title bg-light text-secondary rounded-circle fs-24">
                   <i class="ri-upload-cloud-2-line"></i>
                 </div>
               </div>
               <h5 class="fs-15 mb-0">Thả tập tin vào đây hoặc click để tải lên.</h5>
             </div>
          </div>
        </div>

        <div v-if="images && images.length > 0" class="row g-2">
          <div v-for="(img, idx) in images" :key="idx" class="col-3">
             <div class="position-relative border rounded p-1">
               <img :src="img" class="img-fluid rounded" />
               <BButton v-if="!readonly" variant="danger" size="sm" class="position-absolute top-0 end-0 m-1 p-0 rounded-circle" style="width: 20px; height: 20px" @click="removeGalleryImage(idx)">
                 <i class="ri-close-line"></i>
               </BButton>
             </div>
          </div>
        </div>
      </div>
    </BCardBody>
  </BCard>
</template>

<style scoped>
.avatar-md {
  height: 4.5rem;
  width: 4.5rem;
}

.avatar-title {
  align-items: center;
  display: flex;
  font-weight: 500;
  height: 100%;
  justify-content: center;
  width: 100%;
}
</style>
