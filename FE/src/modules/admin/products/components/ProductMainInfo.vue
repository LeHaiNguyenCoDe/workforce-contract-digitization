<script lang="ts">
export default {}
</script>

<script setup lang="ts">
import { Ckeditor } from '@ckeditor/ckeditor5-vue'
import ClassicEditor from '@ckeditor/ckeditor5-build-classic'

defineOptions({ name: 'ProductMainInfo' })

const props = defineProps<{
  errors?: Record<string, string>
  readonly?: boolean
}>()

const name = defineModel<string>('name', { default: '' })
const description = defineModel<string>('description', { default: '' })

const editor = ClassicEditor
const ckeditor = Ckeditor
const ckConfig = {
  toolbar: [ 
    'undo', 'redo', '|', 'heading', '|', 'bold', 'italic', 'link', 'imageUpload', 'insertTable', 'blockQuote', 'mediaEmbed', '|', 'bulletedList', 'numberedList', 'outdent', 'indent' 
  ]
}
</script>

<template>
  <BCard no-body class="border-0 shadow-sm mb-4">
    <BCardBody class="p-4">
      <div class="mb-4">
        <label class="form-label fw-bold text-muted fs-13 mb-2">Product Title</label>
        <input type="text" class="form-control" :class="{'is-invalid': errors?.name}" v-model="name" :disabled="readonly" placeholder="Enter product title" />
        <div v-if="errors?.name" class="invalid-feedback">{{ errors.name }}</div>
      </div>

      <div class="mb-0">
        <label class="form-label fw-bold text-muted fs-13 mb-2">Product Description</label>
        <ckeditor 
          :editor="editor" 
          v-model="description" 
          :config="ckConfig"
          :disabled="readonly"
        />
      </div>
    </BCardBody>
  </BCard>
</template>

<style scoped>
:deep(.ck-editor__editable) {
  min-height: 300px;
}
</style>
