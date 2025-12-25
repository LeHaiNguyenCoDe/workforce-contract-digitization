<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import BaseModal from '@/shared/components/BaseModal.vue'
import { useProductStore } from '../store/store'
import { useProducts } from '../composables/useProducts'

const { t } = useI18n()

// Store
const store = useProductStore()

// Composables
const {
  searchQuery,
  showModal,
  editingProduct,
  filteredProducts,
  formatPrice,
  setSearchQuery,
  openCreateModal,
  openEditModal,
  saveProduct,
  deleteProduct,
  changePage,
  handleNameChange
} = useProducts()

// Computed from store
const products = computed(() => store.products)
const categories = computed(() => store.categories)
const isLoading = computed(() => store.isLoading)
const isSaving = computed(() => store.isSaving)
const currentPage = computed(() => store.currentPage)
const totalPages = computed(() => store.totalPages)
// Use store.productForm directly - it's already a ref
// productForm is accessed directly via store.productForm in template

// Lifecycle
onMounted(async () => {
  await store.fetchCategories()
  await store.fetchProducts()
})
</script>

<template>
  <div class="h-full flex flex-col p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6 flex-shrink-0">
      <div>
        <h1 class="text-2xl font-bold text-white">{{ t('admin.products') }}</h1>
        <p class="text-slate-400 mt-1">Quản lý danh sách sản phẩm</p>
        <div class="mt-2 bg-info/10 border border-info/20 rounded-lg p-2 text-xs text-info max-w-2xl">
          <strong>Lưu ý:</strong> Trang này quản lý danh sách sản phẩm (master data). 
          Để thêm tồn kho cho sản phẩm, vui lòng sử dụng quy trình 
          <router-link :to="{ name: 'admin-warehouse-inbound-batches' }" class="underline font-semibold hover:text-info-light">
            Kho hàng → Lô nhập
          </router-link>
          (Tạo lô nhập → Nhận hàng → QC → Tự động tạo tồn kho). 
          Xem sản phẩm trong kho tại <router-link :to="{ name: 'admin-warehouse-products' }" class="underline font-semibold hover:text-info-light">Kho hàng → Sản phẩm</router-link>.
        </div>
      </div>
      <button @click="openCreateModal" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2">
          <path d="M5 12h14" />
          <path d="M12 5v14" />
        </svg>
        {{ t('common.create') }}
      </button>
    </div>

    <!-- Search -->
    <div class="bg-dark-800 rounded-xl border border-white/10 p-4 mb-6 flex-shrink-0">
      <div class="flex gap-4">
        <div class="relative flex-1">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"
            xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8" />
            <path d="m21 21-4.3-4.3" />
          </svg>
          <input :value="searchQuery" @input="setSearchQuery(($event.target as HTMLInputElement).value)"
            @keyup.enter="store.fetchProducts({ search: searchQuery })" type="text" class="form-input pl-10"
            placeholder="Tìm kiếm sản phẩm..." />
        </div>
        <button @click="store.fetchProducts({ search: searchQuery })" class="btn btn-secondary">Tìm kiếm</button>
      </div>
    </div>

    <!-- Table Container -->
    <div class="flex-1 min-h-0 bg-dark-800 rounded-xl border border-white/10 overflow-hidden flex flex-col">
      <div v-if="isLoading" class="flex-1 flex items-center justify-center">
        <div class="inline-block w-8 h-8 border-4 border-primary/20 border-t-primary rounded-full animate-spin">
        </div>
      </div>

      <div v-else class="flex-1 overflow-auto">
        <table class="w-full">
          <thead class="sticky top-0 z-10 bg-dark-700">
            <tr class="border-b border-white/10">
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Sản phẩm</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Danh mục</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Giá</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Tồn kho</th>
              <th class="px-6 py-4 text-right text-sm font-semibold text-slate-400">{{ t('common.actions') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5">
            <tr v-for="product in filteredProducts" :key="product.id" class="hover:bg-white/5 transition-colors">
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <img v-if="product.thumbnail" :src="product.thumbnail" :alt="product.name"
                    class="w-12 h-12 rounded-lg object-cover" />
                  <div v-else class="w-12 h-12 rounded-lg bg-slate-700 flex items-center justify-center text-slate-500">
                    📦
                  </div>
                  <div>
                    <p class="font-medium text-white">{{ product.name }}</p>
                    <p class="text-xs text-slate-500">{{ product.slug }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 text-slate-400">{{ product.category?.name || 'N/A' }}</td>
              <td class="px-6 py-4">
                <div class="flex flex-col">
                  <span class="font-semibold text-primary-light">{{ formatPrice(product.price) }}</span>
                  <span v-if="product.sale_price" class="text-xs text-slate-500 line-through">
                    {{ formatPrice(product.sale_price) }}
                  </span>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                  <span :class="[
                    'px-2.5 py-1 rounded-full text-xs font-medium',
                    (product.stock_quantity || 0) > 0 ? 'bg-success/10 text-success' : 'bg-error/10 text-error'
                  ]">
                    {{ product.stock_quantity || 0 }}
                  </span>
                  <router-link 
                    v-if="(product.stock_quantity || 0) === 0"
                    :to="{ name: 'admin-warehouse-inbound-batches' }"
                    class="text-xs text-info hover:text-info-light underline"
                    title="Thêm tồn kho qua quy trình kho hàng"
                  >
                    Thêm tồn kho
                  </router-link>
                </div>
              </td>
              <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button @click="openEditModal(product)"
                    class="w-8 h-8 rounded-lg bg-info/10 text-info hover:bg-info/20 flex items-center justify-center"
                    title="Sửa">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                      stroke="currentColor" stroke-width="2">
                      <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                      <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                    </svg>
                  </button>
                  <button @click="deleteProduct(product.id)"
                    class="w-8 h-8 rounded-lg bg-error/10 text-error hover:bg-error/20 flex items-center justify-center"
                    title="Xóa">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                      stroke="currentColor" stroke-width="2">
                      <path d="M3 6h18" />
                      <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                      <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-if="!filteredProducts.length" class="py-16 text-center">
          <p class="text-slate-400">Không có sản phẩm nào</p>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="border-t border-white/10 p-4 flex items-center justify-between">
        <span class="text-sm text-slate-400">Trang {{ currentPage }} / {{ totalPages }}</span>
        <div class="flex gap-2">
          <button @click="changePage(currentPage - 1)" :disabled="currentPage <= 1"
            class="btn btn-secondary btn-sm" :class="{ 'opacity-50 cursor-not-allowed': currentPage <= 1 }">
            Trước
          </button>
          <button @click="changePage(currentPage + 1)" :disabled="currentPage >= totalPages"
            class="btn btn-secondary btn-sm"
            :class="{ 'opacity-50 cursor-not-allowed': currentPage >= totalPages }">
            Sau
          </button>
        </div>
      </div>
    </div>

    <!-- Product Modal -->
    <BaseModal v-model="showModal" :title="editingProduct ? 'Sửa sản phẩm' : 'Thêm sản phẩm'" size="lg">
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Tên sản phẩm *</label>
          <input v-model="store.productForm.name" @input="handleNameChange" type="text" class="form-input"
            placeholder="Nhập tên sản phẩm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Slug</label>
          <input v-model="store.productForm.slug" type="text" class="form-input" placeholder="Tự động tạo từ tên" />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Danh mục *</label>
            <select v-model="store.productForm.category_id" class="form-input">
              <option value="">Chọn danh mục</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id.toString()">{{ cat.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Giá *</label>
            <input v-model.number="store.productForm.price" type="number" min="0" step="1" class="form-input" 
              placeholder="0" />
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Giá khuyến mãi</label>
          <input v-model="store.productForm.sale_price" type="number" min="0" class="form-input" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Mô tả ngắn</label>
          <textarea v-model="store.productForm.short_description" rows="2" class="form-input"
            placeholder="Mô tả ngắn về sản phẩm"></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Mô tả</label>
          <textarea v-model="store.productForm.description" rows="4" class="form-input"
            placeholder="Mô tả chi tiết về sản phẩm"></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Hình ảnh (URL)</label>
          <input v-model="store.productForm.thumbnail" type="text" class="form-input" placeholder="URL hình ảnh" />
        </div>
        <div>
          <label class="flex items-center gap-2">
            <input v-model="store.productForm.is_active" type="checkbox" class="form-checkbox" />
            <span class="text-sm text-slate-300">Kích hoạt</span>
          </label>
        </div>
        <div class="flex gap-3 pt-4">
          <button @click="showModal = false" class="btn btn-secondary flex-1">Hủy</button>
          <button @click="saveProduct" :disabled="isSaving" class="btn btn-primary flex-1">
            {{ isSaving ? 'Đang lưu...' : 'Lưu' }}
          </button>
        </div>
      </div>
    </BaseModal>
  </div>
</template>
