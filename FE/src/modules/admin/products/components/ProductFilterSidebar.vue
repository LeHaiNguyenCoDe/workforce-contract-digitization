<script setup lang="ts">
import { ref, computed } from 'vue'
import { useAdminProductStore } from '../store/store'

const props = defineProps<{
  categories: any[]
  formatPrice: (price: number) => string
}>()

const selectedCategoryId = defineModel<number | null>('categoryId')
const priceRange = defineModel<{ min: number; max: number }>('priceRange', {
  default: () => ({ min: 0, max: 20000000 })
})
const selectedBrands = defineModel<string[]>('brands', {
  default: () => []
})
const selectedRating = defineModel<number | null>('rating')

const emit = defineEmits(['clear-all', 'remove-filter'])

const store = useAdminProductStore()

// Collapsible states
const isProductsOpen = ref(true)
const isBrandsOpen = ref(true)
const isDiscountOpen = ref(true)
const isRatingOpen = ref(true)

const brandSearchQuery = ref('')
const brandList = ['Boat', 'OnePlus', 'Realme', 'Sony', 'JBL']
const filteredBrands = computed(() => {
    return brandList.filter(b => b.toLowerCase().includes(brandSearchQuery.value.toLowerCase()))
})

const activeFilterTags = computed(() => {
  const tags: Array<{ label: string, type: 'category' | 'brand' | 'price' | 'rating' }> = []
  
  // Category
  if (selectedCategoryId.value) {
    const cat = props.categories.find(c => c.id === selectedCategoryId.value)
    if (cat) tags.push({ label: cat.name, type: 'category' })
  }
  
  // Brands
  selectedBrands.value.forEach(brand => {
    tags.push({ label: brand, type: 'brand' })
  })
  
  // Price (only if not default)
  if (priceRange.value.min > 0 || priceRange.value.max < 20000000) {
    tags.push({ label: `${props.formatPrice(priceRange.value.min)} - ${props.formatPrice(priceRange.value.max)}`, type: 'price' })
  }

  // Rating
  if (selectedRating.value) {
    tags.push({ label: `${selectedRating.value} & Above Star`, type: 'rating' })
  }

  return tags
})

const removeFilter = (tag: any) => {
  emit('remove-filter', tag)
}

const clearAllFilters = () => {
  emit('clear-all')
}
</script>

<template>
  <BCard no-body class="border-0 shadow-sm mb-4 p-0">
    <BCardBody class="p-3">
      <div class="d-flex align-items-center justify-content-between mb-3 pb-1 border-bottom border-light">
        <h5 class="card-title mb-0 fs-13 fw-semibold text-muted text-uppercase ls-1">Filters</h5>
        <BLink href="javascript:void(0);" @click="clearAllFilters" class="text-muted text-decoration-underline small">Clear All</BLink>
      </div>
    
      <div class="filter-content-scroll" style="max-height: calc(80vh - 180px); overflow-y: auto; overflow-x: hidden; padding-right: 5px;">
         <!-- Active Filter Tags -->
        <div v-if="activeFilterTags.length" class="d-flex flex-wrap gap-2 mb-4">
            <BBadge v-for="tag in activeFilterTags" :key="tag.label" 
                    class="bg-primary-subtle text-white rounded-1 d-flex align-items-center gap-1 px-2 py-1 fs-11" style="background-color: #405189 !important;">
                {{ tag.label }} <i class="ri-close-line cursor-pointer" @click="removeFilter(tag)"></i>
            </BBadge>
        </div>

        <div class="mb-4">
          <a @click="isProductsOpen = !isProductsOpen" class="d-flex align-items-center justify-content-between text-muted fw-semibold text-uppercase fs-11 ls-1 cursor-pointer text-decoration-none mb-3">
            <span>PRODUCTS</span>
            <i :class="isProductsOpen ? 'ri-arrow-up-s-line fs-14' : 'ri-arrow-down-s-line fs-14'"></i>
          </a>
          <div v-show="isProductsOpen" class="list-group list-group-flush border-0">
            <a href="javascript:void(0);" v-for="cat in store.categories.slice(0, 8)" :key="cat.id" 
               class="list-group-item list-group-item-action border-0 px-0 py-1 fs-13 d-flex align-items-center justify-content-between text-muted"
               @click="selectedCategoryId = cat.id">
              {{ cat.name }}
              <BBadge v-if="cat.id % 2 === 0" variant="light" class="text-muted fw-normal rounded-circle px-1" style="background-color: #f3f3f9; border: 1px solid #e9ebec;">{{ cat.id + 3 }}</BBadge>
            </a>
          </div>
        </div>

        <hr class="my-3 mx-n3 border-light opacity-50">

        <div class="mb-4">
          <h6 class="text-uppercase fs-11 fw-semibold text-muted mb-3 ls-1">PRICE</h6>
          <div class="px-2 mb-4">
            <div class="slider-teal">
              <input type="range" class="form-range" v-model="priceRange.min" min="0" max="20000000" step="100000">
              <input type="range" class="form-range mt-n2" v-model="priceRange.max" min="0" max="20000000" step="100000">
            </div>
          </div>
          <BRow class="gx-2">
            <BCol>
              <div class="input-group input-group-sm mb-3">
                <span class="input-group-text border-light bg-light text-muted fw-medium small">$</span>
                <input type="text" class="form-control border-light bg-light px-1" v-model="priceRange.min" style="font-size: 11px;">
              </div>
            </BCol>
            <BCol md="auto" class="align-self-center mb-3">
              <span class="text-muted small">to</span>
            </BCol>
            <BCol>
              <div class="input-group input-group-sm mb-3">
                <span class="input-group-text border-light bg-light text-muted fw-medium small">$</span>
                <input type="text" class="form-control border-light bg-light px-1" v-model="priceRange.max" style="font-size: 11px;">
              </div>
            </BCol>
          </BRow>
        </div>

        <hr class="my-3 mx-n3 border-light opacity-50">

        <!-- Brands -->
        <div class="mb-2">
            <a @click="isBrandsOpen = !isBrandsOpen" class="d-flex align-items-center justify-content-between text-muted fw-semibold text-uppercase fs-11 ls-1 cursor-pointer text-decoration-none">
              <span class="d-flex align-items-center">BRANDS <BBadge class="ms-2 rounded-circle d-flex align-items-center justify-content-center p-0 fw-medium" style="background-color: #0ab39c !important; color: white !important; width: 17px; height: 17px; font-size: 9px;">2</BBadge></span>
              <i :class="isBrandsOpen ? 'ri-arrow-up-s-line fs-14' : 'ri-arrow-down-s-line fs-14'"></i>
            </a>
            <div v-show="isBrandsOpen" class="mt-3 transition-all">
                <div class="search-box mb-3">
                    <input type="text" class="form-control form-control-sm border-light py-2" v-model="brandSearchQuery" placeholder="Search Brands..." style="background-color: #f3f6f9 !important; font-size: 12px; border-radius: 4px;">
                    <i class="ri-search-line search-icon text-muted" style="font-size: 12px; left: 10px;"></i>
                </div>
                <div class="brand-list">
                    <div v-for="brand in filteredBrands" :key="brand" class="form-check mb-2">
                        <input class="form-check-input border-light" type="checkbox" :value="brand" :id="'brand_'+brand" v-model="selectedBrands" style="border-radius: 3px;">
                        <label class="form-check-label text-muted fs-13" :for="'brand_'+brand">{{ brand }}</label>
                    </div>
                    <a href="javascript:void(0);" class="text-primary text-decoration-underline small fw-bold mt-1 d-block">1,235 MORE</a>
                </div>
            </div>
        </div>

        <hr class="my-3 mx-n3 border-light opacity-50">

        <!-- Discount -->
        <div class="mb-2">
            <a @click="isDiscountOpen = !isDiscountOpen" class="d-flex align-items-center justify-content-between text-muted fw-semibold text-uppercase fs-11 ls-1 cursor-pointer text-decoration-none">
              <span class="d-flex align-items-center">DISCOUNT <BBadge class="ms-2 rounded-circle d-flex align-items-center justify-content-center p-0 fw-medium" style="background-color: #0ab39c !important; color: white !important; width: 17px; height: 17px; font-size: 9px;">1</BBadge></span>
              <i :class="isDiscountOpen ? 'ri-arrow-up-s-line fs-14' : 'ri-arrow-down-s-line fs-14'"></i>
            </a>
            <div v-show="isDiscountOpen" class="mt-3 transition-all">
                <div class="form-check mb-2">
                    <input class="form-check-input border-light" type="checkbox" value="50" id="disc50" style="border-radius: 3px;">
                    <label class="form-check-label text-muted fs-13" for="disc50">50% or more</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input border-light" type="checkbox" value="40" id="disc40" style="border-radius: 3px;">
                    <label class="form-check-label text-muted fs-13" for="disc40">40% or more</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input border-light" type="checkbox" value="30" id="disc30" style="border-radius: 3px;">
                    <label class="form-check-label text-muted fs-13" for="disc30">30% or more</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input border-light" type="checkbox" value="20" id="disc20" checked style="border-radius: 3px;">
                    <label class="form-check-label text-muted fs-13" for="disc20">20% or more</label>
                </div>
            </div>
        </div>

        <hr class="my-3 mx-n3 border-light opacity-50">

        <!-- Rating -->
        <div class="mb-2">
            <a @click="isRatingOpen = !isRatingOpen" class="d-flex align-items-center justify-content-between text-muted fw-semibold text-uppercase fs-11 ls-1 cursor-pointer text-decoration-none">
              <span class="d-flex align-items-center">RATING <BBadge class="ms-2 rounded-circle d-flex align-items-center justify-content-center p-0 fw-medium" style="background-color: #0ab39c !important; color: white !important; width: 17px; height: 17px; font-size: 9px;">1</BBadge></span>
              <i :class="isRatingOpen ? 'ri-arrow-up-s-line fs-14' : 'ri-arrow-down-s-line fs-14'"></i>
            </a>
            <div v-show="isRatingOpen" class="mt-3 transition-all">
                <div v-for="star in [4, 3, 2, 1]" :key="star" class="form-check mb-2 d-flex align-items-center gap-2">
                    <input class="form-check-input border-light" type="checkbox" :id="'star_'+star" :value="star" v-model="selectedRating" style="border-radius: 3px;">
                    <label class="form-check-label text-muted fs-13 d-flex align-items-center gap-1" :for="'star_'+star">
                        <span class="text-warning d-flex align-items-center" style="font-size: 11px;">
                            <i v-for="i in 5" :key="i" :class="i <= star ? 'ri-star-fill' : 'ri-star-line'"></i>
                        </span>
                        <span v-if="star < 5" class="ms-1 pt-0" style="font-size: 12px;">{{ star }} & Above</span>
                    </label>
                </div>
            </div>
        </div>
      </div>
    </BCardBody>
  </BCard>
</template>

<style scoped>
.filter-content-scroll::-webkit-scrollbar {
    width: 4px;
}
.filter-content-scroll::-webkit-scrollbar-track {
    background: transparent;
}
.filter-content-scroll::-webkit-scrollbar-thumb {
    background: #e2e5e8;
    border-radius: 10px;
}
.filter-content-scroll::-webkit-scrollbar-thumb:hover {
    background: #ced4da;
}
</style>
