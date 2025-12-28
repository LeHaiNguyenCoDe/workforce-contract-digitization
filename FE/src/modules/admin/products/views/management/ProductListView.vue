<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { productColumns } from '../../configs/columns'

const { t } = useI18n()

// Store
const store = useAdminProductStore()

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

// Lifecycle
onMounted(async () => {
  await store.fetchCategories()
  await store.fetchProducts()
})
</script>

<template>
  <div class="h-full flex flex-col p-6">
    <!-- Header -->
    <AdminPageHeader :title="t('admin.products')" description="Quản lý danh sách sản phẩm (master data)">
      <template #actions>
        <DButton variant="primary" @click="openCreateModal">
          <img src="@/assets/admin/icons/plus.svg" class="w-5 h-5 mr-2 brightness-0 invert" alt="Add" />
          {{ t('common.create') }}
        </DButton>
      </template>
    </AdminPageHeader>

    <div class="mb-4 bg-info/10 border border-info/20 rounded-xl p-3 text-xs text-info flex items-start gap-3">
      <img src="@/assets/admin/icons/info.svg" class="w-4 h-4 mt-0.5 opacity-80" alt="Info" />
      <div>
        <strong>Lưu ý:</strong> Trang này quản lý danh mục sản phẩm. Thêm tồn kho tại
        <router-link :to="{ name: 'admin-warehouse-inbound-batches' }" class="underline font-semibold mx-1">Lô nhập</router-link>
        và xem tại <router-link :to="{ name: 'admin-warehouse-inventory' }" class="underline font-semibold ml-1">Tồn kho</router-link>.
      </div>
    </div>

    <!-- Search -->
    <AdminSearch :modelValue="searchQuery" @update:modelValue="setSearchQuery" @search="store.fetchProducts({ search: searchQuery })" placeholder="Tìm kiếm sản phẩm..." />

    <!-- Table -->
    <AdminTable :columns="productColumns" :data="filteredProducts" :loading="isLoading" empty-text="Không có sản phẩm nào">
      <template #cell-name="{ item }">
        <div class="flex items-center gap-3">
          <img v-if="item.thumbnail" :src="item.thumbnail" :alt="item.name" class="w-10 h-10 rounded-lg object-cover border border-white/5" />
          <div v-else class="w-10 h-10 rounded-lg bg-slate-700 flex items-center justify-center text-slate-500">📦</div>
          <div>
            <p class="font-medium text-white">{{ item.name }}</p>
            <p class="text-xs text-slate-500">{{ item.slug }}</p>
          </div>
        </div>
      </template>

      <template #cell-category="{ item }">
        <span class="text-slate-400">{{ item.category?.name || 'N/A' }}</span>
      </template>

      <template #cell-price="{ item }">
        <div class="flex flex-col">
          <span class="font-semibold text-primary-light">{{ formatPrice(item.price) }}</span>
          <span v-if="item.sale_price" class="text-xs text-slate-500 line-through">{{ formatPrice(item.sale_price) }}</span>
        </div>
      </template>

      <template #cell-stock_quantity="{ item }">
        <div class="flex items-center gap-2">
          <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', (item.stock_quantity || 0) > 0 ? 'bg-success/10 text-success' : 'bg-error/10 text-error']">
            {{ item.stock_quantity || 0 }}
          </span>
          <router-link v-if="(item.stock_quantity || 0) === 0" :to="{ name: 'admin-warehouse-inbound-batches' }" class="text-[10px] text-info hover:underline">Nhập hàng</router-link>
        </div>
      </template>

      <template #actions="{ item }">
        <div class="flex items-center justify-end gap-1">
          <DAction icon="edit" @click.stop="openEditModal(item)" />
          <DAction icon="delete" variant="danger" @click.stop="deleteProduct(item.id)" />
        </div>
      </template>

      <template #footer>
        <Pagination :currentPage="currentPage" :totalPages="totalPages" @page-change="changePage" />
      </template>
    </AdminTable>

    <!-- Modal -->
    <DModal v-model="showModal" :title="editingProduct ? 'Sửa sản phẩm' : 'Thêm sản phẩm'" size="lg">
      <div class="grid grid-cols-2 gap-4">
        <div class="col-span-2">
          <label class="block text-sm font-medium text-slate-300 mb-2">Tên sản phẩm *</label>
          <input v-model="store.productForm.name" @input="handleNameChange" type="text" class="form-input" placeholder="Nhập tên sản phẩm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Slug</label>
          <input v-model="store.productForm.slug" type="text" class="form-input" placeholder="Tự động tạo từ tên" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Danh mục *</label>
          <select v-model="store.productForm.category_id" class="form-input">
            <option value="">Chọn danh mục</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id.toString()">{{ cat.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Giá *</label>
          <input v-model.number="store.productForm.price" type="number" min="0" step="1" class="form-input" placeholder="0" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Giá khuyến mãi</label>
          <input v-model="store.productForm.sale_price" type="number" min="0" class="form-input" placeholder="0" />
        </div>
        <div class="col-span-2">
          <label class="block text-sm font-medium text-slate-300 mb-2">Mô tả ngắn</label>
          <textarea v-model="store.productForm.short_description" rows="2" class="form-input" placeholder="Mô tả ngắn..."></textarea>
        </div>
        <div class="col-span-2">
          <label class="block text-sm font-medium text-slate-300 mb-2">Mô tả chi tiết</label>
          <textarea v-model="store.productForm.description" rows="4" class="form-input" placeholder="Mô tả chi tiết..."></textarea>
        </div>
        <div class="col-span-2">
          <label class="block text-sm font-medium text-slate-300 mb-2">Hình ảnh (URL)</label>
          <input v-model="store.productForm.thumbnail" type="text" class="form-input" placeholder="URL hình ảnh" />
        </div>
        <div class="col-span-2">
          <label class="flex items-center gap-2 cursor-pointer">
            <input v-model="store.productForm.is_active" type="checkbox" class="form-checkbox" />
            <span class="text-sm text-slate-300">Kích hoạt sản phẩm</span>
          </label>
        </div>
      </div>

      <template #footer>
        <div class="flex gap-3">
          <DButton variant="secondary" class="flex-1" @click="showModal = false">Hủy</DButton>
          <DButton variant="primary" class="flex-1" :loading="isSaving" @click="saveProduct">Lưu thay đổi</DButton>
        </div>
      </template>
    </DModal>
  </div>
</template>
