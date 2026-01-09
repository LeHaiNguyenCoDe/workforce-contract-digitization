<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import BaseModal from '@/components/BaseModal.vue'

// Store
const store = useWarehouseStore()
const { t } = useI18n()

// Composables
const {
  searchQuery,
  statusFilter,
  currentPage,
  selectedProducts,
  isDeleting,
  showStockModal,
  paginatedProducts,
  totalPages,
  selectedCount,
  isAllSelected,
  isSomeSelected,
  changePage,
  setSearchQuery,
  setStatusFilter,
  toggleSelectAll,
  toggleSelectProduct,
  getStockClass,
  openStockModal,
  handleStockUpdate,
  deleteProduct,
  deleteSelectedProducts
} = useWarehouseProducts()

const { showProductModal, openProductModal, handleProductSave } = useProductModal()

// Computed from store
const isLoading = computed(() => store.isLoading)
const isSubmitting = computed(() => store.isSubmitting)
const selectedProduct = computed(() => store.selectedProduct)
const categories = computed(() => store.categories)
const suppliers = computed(() => store.suppliers)

// Direct access to store forms for v-model (not computed because v-model needs to mutate)
const stockForm = store.stockForm
const productForm = store.productForm

// Lifecycle
onMounted(async () => {
  await store.fetchMetadata()
  await store.fetchProducts()
})
</script>

<template>
  <div class="h-full flex flex-col p-6">
    <div class="flex flex-col h-full max-w-7xl mx-auto w-full">
      <!-- Header -->
      <div class="flex items-center justify-between mb-6 flex-shrink-0">
        <div>
          <h1 class="text-2xl font-bold text-white">{{ t('admin.warehouseProducts') }}</h1>
          <p class="text-slate-400 mt-1">{{ t('admin.manageProductsInventory') }}</p>
        </div>
        <div class="flex gap-3">
          <button v-if="selectedCount > 0" @click="deleteSelectedProducts" class="btn btn-error" :disabled="isDeleting">
            Xóa ({{ selectedCount }})
          </button>
          <button @click="openProductModal()" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2">
              <path d="M12 5v14" />
              <path d="M5 12h14" />
            </svg>
            {{ t('admin.addProduct') }}
          </button>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-dark-800 rounded-xl border border-white/10 p-4 mb-6 flex-shrink-0">
        <div class="flex gap-4">
          <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" xmlns="http://www.w3.org/2000/svg"
              width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8" />
              <path d="m21 21-4.3-4.3" />
            </svg>
            <input :value="searchQuery" @input="setSearchQuery(($event.target as HTMLInputElement).value)" type="text"
              :placeholder="t('admin.searchProductsPlaceholder')"
              class="w-full pl-10 pr-4 py-2.5 bg-dark-700 border border-white/10 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary" />
          </div>
          <select :value="statusFilter" @change="setStatusFilter(($event.target as HTMLSelectElement).value)"
            class="px-4 py-2.5 bg-dark-700 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary">
            <option value="">{{ t('common.allStatuses') }}</option>
            <option value="stock">{{ t('common.active') }}</option>
            <option value="out_of_stock">{{ t('admin.outOfStock') }}</option>
            <option value="low_stock">{{ t('admin.lowStock') }}</option>
          </select>
        </div>
      </div>

      <!-- Products Table -->
      <div class="flex-1 min-h-0 bg-dark-800 rounded-xl border border-white/10 overflow-hidden flex flex-col">
        <div v-if="isLoading" class="flex-1 flex items-center justify-center py-16">
          <p class="text-slate-400">{{ t('common.loading') }}</p>
        </div>

        <div v-else class="flex-1 overflow-auto">
          <table class="w-full min-w-[1200px]">
            <thead class="sticky top-0 z-10 bg-dark-700">
              <tr class="border-b border-white/10">
                <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400 w-12">
                  <input type="checkbox" :checked="isAllSelected" :indeterminate="isSomeSelected && !isAllSelected"
                    @change="toggleSelectAll"
                    class="w-4 h-4 rounded border-white/20 bg-dark-800 text-primary focus:ring-primary" />
                </th>
                <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400">{{ t('admin.productName') }}</th>
                <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400">SKU</th>
                <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400">{{ t('admin.categories') }}</th>
                <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400">{{ t('admin.suppliers') }}</th>
                <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400">{{ t('admin.inventory') }}</th>
                <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400">{{ t('admin.availableStock') }}
                </th>
                <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400">{{ t('admin.minStock') }}</th>
                <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400">{{ t('admin.location') }}</th>
                <th class="px-4 py-4 text-left text-sm font-semibold text-slate-400">{{ t('admin.storageType') }}</th>
                <th class="px-4 py-4 text-right text-sm font-semibold text-slate-400">{{ t('common.actions') }}</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
              <tr v-for="product in paginatedProducts" :key="product.id" class="hover:bg-white/5 transition-colors"
                :class="{ 'bg-primary/5': selectedProducts.has(product.id) }">
                <td class="px-4 py-4">
                  <input type="checkbox" :checked="selectedProducts.has(product.id)"
                    @change="toggleSelectProduct(product.id)"
                    class="w-4 h-4 rounded border-white/20 bg-dark-800 text-primary focus:ring-primary" />
                </td>
                <td class="px-4 py-4">
                  <p class="font-medium text-white">{{ product.name }}</p>
                </td>
                <td class="px-4 py-4">
                  <code class="text-xs font-mono text-primary">{{ product.sku }}</code>
                </td>
                <td class="px-4 py-4">
                  <span class="text-sm text-slate-300">{{ product.category || t('admin.uncategorized') }}</span>
                </td>
                <td class="px-4 py-4">
                  <span class="text-sm text-white">{{ product.supplier || '-' }}</span>
                </td>
                <td class="px-4 py-4">
                  <span :class="['px-2.5 py-1 rounded-full text-xs font-medium', getStockClass(product)]">
                    {{ Number(product.quantity) || 0 }}
                  </span>
                </td>
                <td class="px-4 py-4">
                  <span
                    :class="['px-2.5 py-1 rounded-full text-xs font-medium',
                      (Number(product.available_quantity) || 0) > 0 ? 'bg-success/10 text-success' : 'bg-slate-500/10 text-slate-400']">
                    {{ Number(product.available_quantity) || 0 }}
                  </span>
                </td>
                <td class="px-4 py-4">
                  <span class="text-sm text-slate-400">{{ Number(product.minStock) || 5 }}</span>
                </td>
                <td class="px-4 py-4">
                  <span class="text-sm text-slate-400">{{ product.location || '-' }}</span>
                </td>
                <td class="px-4 py-4">
                  <span :class="[
                    'px-2.5 py-1 rounded-full text-xs font-medium',
                    product.status === 'stock' ? 'bg-success/10 text-success' :
                      product.status === 'out_of_stock' ? 'bg-error/10 text-error' :
                        product.status === 'low_stock' ? 'bg-warning/10 text-warning' :
                          'bg-slate-500/10 text-slate-400'
                  ]">
                    {{ product.status === 'stock' ? t('common.active') :
                      product.status === 'out_of_stock' ? t('admin.outOfStock') :
                        product.status === 'low_stock' ? t('admin.lowStock') : product.status || t('common.active') }}
                  </span>
                </td>
                <td class="px-4 py-4 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <button @click="openProductModal(product)"
                      class="w-8 h-8 rounded-lg bg-info/10 text-info hover:bg-info/20 flex items-center justify-center"
                      title="Sửa">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                      </svg>
                    </button>
                    <button @click="openStockModal(product, 'outbound')"
                      class="w-8 h-8 rounded-lg bg-warning/10 text-warning hover:bg-warning/20 flex items-center justify-center"
                      title="Xuất kho" :disabled="(Number(product.available_quantity) || 0) <= 0">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14" />
                        <path d="M12 19V5" />
                      </svg>
                    </button>
                    <button @click="openStockModal(product, 'adjust')"
                      class="w-8 h-8 rounded-lg bg-info/10 text-info hover:bg-info/20 flex items-center justify-center"
                      title="Điều chỉnh">
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

          <div v-if="!paginatedProducts.length && !isLoading" class="py-16 text-center">
            <p class="text-slate-400">{{ t('admin.noProductsFound') }}</p>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1"
          class="flex items-center justify-center gap-2 p-4 border-t border-white/10 flex-shrink-0">
          <button @click="changePage(currentPage - 1)" :disabled="currentPage <= 1" class="btn btn-secondary btn-sm"
            :class="{ 'opacity-50 cursor-not-allowed': currentPage <= 1 }">
            {{ t('common.previous') }}
          </button>
          <span class="text-slate-400 text-sm">{{ currentPage }} / {{ totalPages }}</span>
          <button @click="changePage(currentPage + 1)" :disabled="currentPage >= totalPages"
            class="btn btn-secondary btn-sm" :class="{ 'opacity-50 cursor-not-allowed': currentPage >= totalPages }">
            {{ t('common.next') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Stock Modal -->
    <BaseModal v-model="showStockModal" :title="stockForm.type === 'outbound' ? `${t('admin.outbound')}: ${selectedProduct?.name}` :
      stockForm.type === 'adjust' ? `${t('admin.stockAdjustment')}: ${selectedProduct?.name}` :
        `${t('common.importWarehouse')}: ${selectedProduct?.name}`" size="md">
      <div v-if="selectedProduct" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">{{ t('common.product') }}</label>
          <input :value="selectedProduct.name" type="text" disabled
            class="form-input bg-dark-700 text-slate-400 cursor-not-allowed" />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">{{ t('admin.currentStock') }}</label>
            <div class="px-4 py-2.5 bg-dark-700 border border-white/10 rounded-lg">
              <span class="text-lg font-semibold text-primary">{{ Number(selectedProduct.quantity) || 0 }}</span>
              <span class="text-sm text-slate-400 ml-2">{{ t('admin.products') }}</span>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">{{ t('admin.availableStock') }}</label>
            <div class="px-4 py-2.5 bg-dark-700 border border-white/10 rounded-lg">
              <span class="text-lg font-semibold text-success">{{ Number(selectedProduct.available_quantity) || 0
              }}</span>
              <span class="text-sm text-slate-400 ml-2">{{ t('admin.products') }}</span>
            </div>
          </div>
        </div>
        <div v-if="stockForm.type === 'outbound'">
          <div class="bg-warning/10 border border-warning/20 rounded-lg p-3 mb-4">
            <p class="text-sm text-warning">
              <strong>{{ t('common.notice') }}:</strong> Only available after QC (Available Inventory).
              {{ t('admin.availableStock') }}: <strong>{{ Number(selectedProduct.available_quantity) || 0 }}</strong>
            </p>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">{{ t('admin.quantityOutbound') }} *</label>
            <input v-model.number="stockForm.quantity" type="number" min="1"
              :max="Number(selectedProduct.available_quantity) || 0" class="form-input" />
            <p class="text-xs text-slate-400 mt-1">
              {{ t('admin.afterOutbound') }}: {{ Math.max(0, (Number(selectedProduct.available_quantity) || 0) -
                (Number(stockForm.quantity) || 0)) }} {{ t('admin.availableForOutbound') }}
            </p>
          </div>
        </div>
        <div v-else-if="stockForm.type === 'adjust'">
          <div class="bg-info/10 border border-info/20 rounded-lg p-3 mb-4">
            <p class="text-sm text-info">
              <strong>{{ t('common.notice') }}:</strong> {{ t('admin.stockAdjustmentDesc') }}
            </p>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">{{ t('admin.newQuantity') }} *</label>
            <input v-model.number="stockForm.quantity" type="number" min="0" class="form-input" />
            <p class="text-xs text-slate-400 mt-1">
              {{ t('admin.currentQuantity') }}: {{ Number(selectedProduct.quantity) || 0 }} → {{ t('admin.newQuantity')
              }}: {{
                Number(stockForm.quantity) || 0 }}
            </p>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">
              {{ t('admin.adjustmentReason') }} * <span class="text-error">({{ t('admin.required') }})</span>
            </label>
            <textarea v-model="stockForm.reason" rows="2" class="form-input"
              :placeholder="t('admin.adjustmentReasonPlaceholder')" required></textarea>
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">{{ t('admin.note') }}</label>
          <textarea v-model="stockForm.note" rows="3" class="form-input"
            :placeholder="t('admin.notePlaceholder')"></textarea>
        </div>
        <div class="flex gap-3 pt-4">
          <button @click="showStockModal = false" class="btn btn-secondary flex-1">{{ t('common.cancel') }}</button>
          <button @click="handleStockUpdate"
            :disabled="isSubmitting || (stockForm.type === 'adjust' && !stockForm.reason)"
            class="btn btn-primary flex-1">
            {{ isSubmitting ? t('common.processing') : t('common.confirm') }}
          </button>
        </div>
      </div>
    </BaseModal>

    <!-- Product Modal -->
    <BaseModal v-model="showProductModal" :title="selectedProduct ? t('admin.editProduct') : t('admin.addProduct')"
      size="lg">
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">{{ t('admin.productName') }} *</label>
          <input v-model="productForm.name" type="text" class="form-input" :placeholder="t('admin.enterProductName')" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">SKU</label>
          <input v-model="productForm.sku" type="text" class="form-input"
            :placeholder="t('admin.skuAutoPlaceholder')" />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">{{ t('admin.categories') }} *</label>
            <select v-model="productForm.category_id" class="form-input">
              <option :value="null">{{ t('common.selectCategory') }}</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">{{ t('admin.suppliers') }}</label>
            <select v-model="productForm.supplier_id" class="form-input">
              <option :value="null">{{ t('common.selectEmployee') }}</option>
              <option v-for="sup in suppliers" :key="sup.id" :value="sup.id">{{ sup.name }}</option>
            </select>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">{{ t('admin.inventory') }}</label>
            <input v-model.number="productForm.stock_qty" type="number" min="0" class="form-input" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">{{ t('admin.minStockLevel') }}</label>
            <input v-model.number="productForm.min_stock_level" type="number" min="0" class="form-input" />
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">{{ t('admin.storageLocation') }}</label>
            <input v-model="productForm.storage_location" type="text" class="form-input"
              :placeholder="t('admin.storageLocationPlaceholder')" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">{{ t('admin.storageType') }}</label>
            <select v-model="productForm.warehouse_type" class="form-input">
              <option value="stock">{{ t('common.active') }}</option>
              <option value="out_of_stock">{{ t('admin.outOfStock') }}</option>
              <option value="low_stock">{{ t('admin.lowStock') }}</option>
            </select>
          </div>
        </div>
        <div class="flex gap-3 pt-4">
          <button @click="showProductModal = false" class="btn btn-secondary flex-1">{{ t('common.cancel') }}</button>
          <button @click="handleProductSave" :disabled="isSubmitting" class="btn btn-primary flex-1">
            {{ isSubmitting ? t('common.saving') : t('common.save') }}
          </button>
        </div>
      </div>
    </BaseModal>
  </div>
</template>
