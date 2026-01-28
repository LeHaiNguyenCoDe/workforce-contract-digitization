<script setup lang="ts">
import { onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useAdminProductStore } from '../../store/store'
import { useAdminProducts } from '../../composables/useAdminProducts'

// Components
import ProductMainInfo from '../../components/ProductMainInfo.vue'
import ProductGallery from '../../components/ProductGallery.vue'
import ProductGeneralInfo from '../../components/ProductGeneralInfo.vue'
import ProductFormSidebar from '../../components/ProductFormSidebar.vue'
import ProductSpecsEditor from '../../components/ProductSpecsEditor.vue'
import ProductVariantsEditor from '../../components/ProductVariantsEditor.vue'
import ProductFAQEditor from '../../components/ProductFAQEditor.vue'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const store = useAdminProductStore()
const { 
  loadProductForForm, 
  saveProduct, 
  generateSlug,
  errors
} = useAdminProducts()


const isEdit = computed(() => !!route.params.id)
const isViewMode = computed(() => route.name === 'admin-products-view')
const productId = computed(() => Number(route.params.id))

// Form state from store (proxied for template)
const form = computed(() => store.productForm)

// Image handling
const handleRemoveGalleryImage = (index: number) => {
  if (isViewMode.value) return
  form.value.images?.splice(index, 1)
}

onMounted(async () => {
  if (store.categories.length === 0) {
    await store.fetchCategories()
  }
  
  if (isEdit.value || isViewMode.value) {
    await loadProductForForm(productId.value)
  } else {
    store.resetProductForm()
  }
})

// Handle name update with auto-slug generation
const handleNameUpdate = (newName: string) => {
  form.value.name = newName
  if (!isEdit.value && !isViewMode.value) {
    form.value.slug = generateSlug(newName)
  }
}

const handleSave = async () => {
  if (isViewMode.value) {
    router.push({ name: 'admin-products-edit', params: { id: productId.value } })
    return
  }
  const success = await saveProduct()
  if (success) {
    router.push({ name: 'admin-products' })
  }
}

const handleCancel = () => {
  router.push({ name: 'admin-products' })
}
</script>

<template>
  <div>
    <PageHeader :title="isViewMode ? 'Chi tiết sản phẩm' : (isEdit ? 'Chỉnh sửa sản phẩm' : 'Tạo sản phẩm')" pageTitle="Ecommerce" />

    <BRow>
      <!-- Main Content Area -->
      <BCol lg="8">
        <ProductMainInfo 
          :name="form.name"
          @update:name="handleNameUpdate"
          :description="form.description"
          @update:description="form.description = $event"
          :errors="errors"
          :readonly="isViewMode"
        />

        <ProductGallery 
          :thumbnail="form.thumbnail"
          @update:thumbnail="form.thumbnail = $event"
          :images="form.images"
          @update:images="form.images = $event"
          @remove-gallery-image="handleRemoveGalleryImage"
          :readonly="isViewMode"
        />

        <ProductGeneralInfo 
          :manufacturerName="form.manufacturer_name"
          @update:manufacturerName="form.manufacturer_name = $event"
          :manufacturerBrand="form.manufacturer_brand"
          @update:manufacturerBrand="form.manufacturer_brand = $event"
          :stockQuantity="form.stock_quantity"
          @update:stockQuantity="form.stock_quantity = $event"
          :price="form.price"
          @update:price="form.price = $event"
          :discountPercentage="form.discount_percentage"
          @update:discountPercentage="form.discount_percentage = $event"
          :ordersCount="form.orders_count"
          @update:ordersCount="form.orders_count = $event"
          :errors="errors"
          :readonly="isViewMode"
        />

        <ProductSpecsEditor 
          v-model="form.specs"
          :readonly="isViewMode"
        />

        <ProductVariantsEditor 
          v-model="form.variants"
          :readonly="isViewMode"
        />

        <ProductFAQEditor 
          v-model="form.faqs"
          :readonly="isViewMode"
        />

        <div class="text-end mb-4 gap-2 d-flex justify-content-end">
          <BButton variant="light" class="px-4" @click="handleCancel">{{ isViewMode ? 'Quay lại' : 'Hủy' }}</BButton>
          <BButton variant="success" class="px-4" @click="handleSave">{{ isViewMode ? 'Chỉnh sửa' : 'Lưu sản phẩm' }}</BButton>
        </div>
      </BCol>

      <!-- Sidebar Area -->
      <BCol lg="4">
        <ProductFormSidebar 
          :categories="store.categories"
          :isActive="form.is_active"
          @update:isActive="form.is_active = $event"
          :visibility="form.visibility"
          @update:visibility="form.visibility = $event"
          :publishedAt="form.published_at"
          @update:publishedAt="form.published_at = $event"
          :categoryId="form.category_id"
          @update:categoryId="form.category_id = $event"
          :tags="form.tags"
          @update:tags="form.tags = $event"
          :shortDescription="form.short_description"
          @update:shortDescription="form.short_description = $event"
          :errors="errors"
          :readonly="isViewMode"
        />
      </BCol>
    </BRow>
  </div>
</template>

<style scoped>
.form-label {
  color: var(--vz-body-color);
}
</style>
