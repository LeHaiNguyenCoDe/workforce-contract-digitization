<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useAdminProductStore } from '../../store/store'
import { useAdminProducts } from '../../composables/useAdminProducts'
import ProductFilterSidebar from '../../components/ProductFilterSidebar.vue'
import ProductTableList from '../../components/ProductTableList.vue'

const { t } = useI18n()
const store = useAdminProductStore()

const {
  searchQuery,
  filteredProducts,
  formatPrice,
  setSearchQuery,
  navigateToCreate,
  navigateToEdit,
  navigateToView,
  deleteProduct,
  deleteProducts,
  changePage,
  selectedCategoryId,
  priceRange,
  selectedBrands,
  selectedRating,
  activeTab
} = useAdminProducts()

// Computed from store
const categories = computed(() => store.categories)
const isLoading = computed(() => store.isLoading)
const currentPage = ref(store.currentPage)

// DTable Columns
const displayColumns = [
  { key: 'selection', label: '#', width: '50px' },
  { key: 'id', label: 'ID', width: '50px', align: 'center' as const },
  { key: 'name', label: 'Product' },
  { key: 'stock_quantity', label: 'Stock', width: '100px', align: 'center' as const },
  { key: 'price', label: 'Price', width: '120px', align: 'center' as const },
  { key: 'orders_count', label: 'Orders', width: '100px', align: 'center' as const },
  { key: 'rating', label: 'Rating', width: '100px', align: 'center' as const },
  { key: 'published_at', label: 'Published', width: '150px', align: 'center' as const },
]

const selectedRows = ref<number[]>([])

// Mock helper for UI display
const enrichedProducts = computed(() => {
  return filteredProducts.value.map((p) => {
    let display_date = 'Draft'
    if (p.is_active) {
      if (p.published_at) {
        const date = new Date(p.published_at)
        const dateStr = date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
        const timeStr = date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true })
        display_date = `${dateStr} <span class="text-muted fs-11 ms-1">${timeStr}</span>`
      } else {
        display_date = 'Published'
      }
    }
    
    return {
      ...p,
      orders_count: p.orders_count || 0,
      rating_val: p.rating?.avg || '0.0',
      display_date
    }
  })
})

const clearAllFilters = () => {
  searchQuery.value = ''
  selectedCategoryId.value = null
  priceRange.value = { min: 0, max: 20000000 }
  selectedBrands.value = []
  selectedRating.value = null
}

const removeFilter = (tag: any) => {
  if (tag.type === 'category') selectedCategoryId.value = null
  if (tag.type === 'brand') selectedBrands.value = selectedBrands.value.filter(b => b !== tag.label)
  if (tag.type === 'price') priceRange.value = { min: 0, max: 20000000 }
  if (tag.type === 'rating') selectedRating.value = null
}

onMounted(async () => {
  await store.fetchCategories()
  await store.fetchProducts()
})

const handlePageChange = (val: number) => {
  changePage(val)
}
</script>

<template>
  <div class="page-content py-4" style="background-color: #f3f3f9;">
    <PageHeader :title="t('admin.products')" pageTitle="Ecommerce" />

    <BRow>
      <!-- Filter Sidebar -->
      <BCol xl="3" lg="4" class="sticky-side-div">
        <ProductFilterSidebar 
          :categoryId="selectedCategoryId"
          @update:categoryId="selectedCategoryId = $event ?? null"
          :priceRange="priceRange"
          @update:priceRange="priceRange = $event"
          :brands="selectedBrands"
          @update:brands="selectedBrands = $event"
          :rating="selectedRating"
          @update:rating="selectedRating = $event ?? null"
          :categories="categories"
          :formatPrice="formatPrice"
          @clear-all="clearAllFilters"
          @remove-filter="removeFilter"
        />
      </BCol>

      <!-- Product List -->
      <BCol xl="9" lg="8">
        <ProductTableList 
          :searchQuery="searchQuery"
          @update:searchQuery="searchQuery = $event"
          :activeTab="activeTab"
          @update:activeTab="activeTab = $event"
          :currentPage="currentPage"
          @update:currentPage="currentPage = $event"
          :selectedRows="selectedRows"
          @update:selectedRows="selectedRows = $event"
          :columns="displayColumns"
          :products="enrichedProducts"
          :isLoading="isLoading"
          :formatPrice="formatPrice"
          @create="navigateToCreate"
          @edit="navigateToEdit"
          @view="navigateToView"
          @delete="deleteProduct"
          @delete-bulk="deleteProducts"
          @change-page="handlePageChange"
          @search-input="setSearchQuery"
        />
      </BCol>
    </BRow>
  </div>
</template>

<style scoped>
.sticky-side-div {
    position: sticky;
    top: 70px;
}
</style>
