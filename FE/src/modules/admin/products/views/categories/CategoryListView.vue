<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { storeToRefs } from 'pinia'
import { useAdminCategoryStore } from '../../store/categoryStore'
import { useAdminCategories } from '../../composables/useAdminCategories'
import CategoryFormModal from './Modal/CategoryFormModal.vue'

const { t } = useI18n()

// Store
const store = useAdminCategoryStore()

// Extract reactive refs from store
const { categories, isLoading } = storeToRefs(store)

const isCategoryActive = (cat: any) => {
  return cat.is_active !== 0 && cat.is_active !== false && cat.is_active !== '0'
}

// Composables
const {
  showModal,
  editingCategory,
  openCreateModal,
  openEditModal,
  saveCategory,
  deleteCategory
} = useAdminCategories()

// Local state
const searchQuery = ref('')
const activeTab = ref(0)
const currentPage = ref(1)
const selectedRows = ref<number[]>([])

// Column config
const displayColumns = [
  { key: 'selection', label: '#', width: '50px' },
  { key: 'name', label: 'Danh mục' },
  { key: 'slug', label: 'Slug', width: '200px' },
  { key: 'products_count', label: 'Sản phẩm', width: '120px', align: 'center' as const },
  { key: 'is_active', label: 'Trạng thái', width: '120px', align: 'center' as const },
  { key: 'created_at', label: 'Ngày tạo', width: '150px', align: 'center' as const },
]

// Computed
const hasSelectedRows = computed(() => selectedRows.value.length > 0)

const filteredCategories = computed(() => {
  let result = categories.value

  // Search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(c => 
      c.name.toLowerCase().includes(query) ||
      c.slug?.toLowerCase().includes(query)
    )
  }

  // Tab filter
  if (activeTab.value === 1) {
    result = result.filter(c => isCategoryActive(c))
  } else if (activeTab.value === 2) {
    result = result.filter(c => !isCategoryActive(c))
  }

  return result
})

const enrichedCategories = computed(() => {
  return filteredCategories.value.map(c => {
    const date = c.created_at ? new Date(c.created_at) : null
    let display_date = '-'
    if (date) {
      const dateStr = date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
      const timeStr = date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true })
      display_date = `${dateStr} <span class="text-muted fs-11 ms-1">${timeStr}</span>`
    }
    
    return {
      ...c,
      products_count: c.products_count || 0,
      display_date
    }
  })
})

// Methods
const handleRemoveSelected = async () => {
  const idsToDelete = [...selectedRows.value]
  store.isLoading = true
  try {
    for (const id of idsToDelete) {
      await store.deleteCategory(id)
    }
    selectedRows.value = []
  } finally {
    store.isLoading = false
  }
}

// Lifecycle
onMounted(async () => {
  await store.fetchCategories()
})
</script>

<template>
  <div class="page-content py-4" style="background-color: #f3f3f9;">
    <PageHeader :title="t('admin.categories')" pageTitle="Ecommerce" />

    <BRow>
      <BCol cols="12">
        <BCard no-body class="border-0 shadow-sm mb-4 overflow-hidden" style="border-radius: 8px;">
          <BCardBody class="p-3 pb-0">
            <BRow class="g-3 align-items-center mb-3">
              <BCol>
                <BButton variant="success" @click="openCreateModal" class="d-flex align-items-center gap-2 px-3 fw-medium" style="background-color: #0ab39c; border-color: #0ab39c;">
                  <i class="ri-add-line align-bottom me-1"></i> Thêm danh mục
                </BButton>
              </BCol>
              <BCol md="auto">
                <div class="search-box">
                  <input type="text" class="form-control border-light" style="width: 250px;" v-model="searchQuery" 
                         placeholder="Tìm kiếm danh mục...">
                  <i class="ri-search-line search-icon text-muted"></i>
                </div>
              </BCol>
            </BRow>

            <!-- Tabs with Selection Actions -->
            <div class="d-flex align-items-center justify-content-between border-bottom">
              <BNav tabs class="nav-tabs-custom border-0">
                  <BNavItem :active="activeTab === 0" @click="activeTab = 0">
                      Tất cả <BBadge variant="primary" class="bg-primary-subtle text-primary ms-1 fs-11 px-1 rounded-1">{{ categories.length }}</BBadge>
                  </BNavItem>
                   <BNavItem :active="activeTab === 1" @click="activeTab = 1">
                       Hiển thị <BBadge variant="success" class="bg-success-subtle text-success ms-1 fs-11 px-1 rounded-1">{{ categories.filter(isCategoryActive).length }}</BBadge>
                   </BNavItem>
                  <BNavItem :active="activeTab === 2" @click="activeTab = 2">
                      Ẩn <BBadge variant="secondary" class="bg-secondary-subtle text-secondary ms-1 fs-11 px-1 rounded-1">{{ categories.filter(c => !isCategoryActive(c)).length }}</BBadge>
                  </BNavItem>
              </BNav>
              
              <!-- Selection Actions -->
              <div v-if="hasSelectedRows" class="d-flex align-items-center gap-3 pb-2">
                <span class="text-muted small">Đã chọn {{ selectedRows.length }}</span>
                <a href="javascript:void(0);" class="text-danger fw-medium small" @click="handleRemoveSelected">Xóa</a>
              </div>
            </div>
          </BCardBody>

          <BCardBody class="p-0">
            <DTable 
              :columns="displayColumns" 
              :data="enrichedCategories" 
              :loading="isLoading"
              row-key="id"
              hoverable
              class="dtable"
            >
              <!-- Selection -->
              <template #cell-selection="{ item }">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" :value="item.id" v-model="selectedRows">
                </div>
              </template>

              <!-- Category Name -->
              <template #cell-name="{ item }">
                <div class="d-flex align-items-center">
                  <div class="flex-shrink-0 me-3">
                    <div class="product-img-wrapper">
                      <div v-if="item.image" class="d-flex align-items-center justify-content-center h-100">
                        <img :src="item.image" :alt="item.name" class="product-img" />
                      </div>
                      <div v-else class="d-flex align-items-center justify-content-center" style="font-size: 22px;">📁</div>
                    </div>
                  </div>
                  <div class="flex-grow-1 overflow-hidden">
                    <h5 class="fs-14 mb-1 text-truncate">
                      <BLink @click="openEditModal(item)" class="text-dark fw-semibold text-decoration-none">{{ item.name }}</BLink>
                    </h5>
                    <p v-if="item.description" class="text-muted mb-0 small text-truncate">{{ item.description }}</p>
                  </div>
                </div>
              </template>

              <!-- Slug -->
              <template #cell-slug="{ item }">
                <code class="text-xs bg-light px-2 py-1 rounded text-muted font-monospace">{{ item.slug || '-' }}</code>
              </template>

              <!-- Products Count -->
              <template #cell-products_count="{ item }">
                <div class="text-center">
                  <span class="badge bg-primary-subtle text-primary px-2 py-1">{{ item.products_count }}</span>
                </div>
              </template>

              <!-- Status -->
              <template #cell-is_active="{ item }">
                <div class="text-center">
                  <span v-if="item.is_active !== 0 && item.is_active !== false" class="badge bg-success-subtle text-success px-2 py-1">
                    <i class="ri-checkbox-circle-line me-1"></i> Hiển thị
                  </span>
                  <span v-else class="badge bg-secondary-subtle text-secondary px-2 py-1">
                    <i class="ri-close-circle-line me-1"></i> Ẩn
                  </span>
                </div>
              </template>

              <!-- Created At -->
              <template #cell-created_at="{ item }">
                <div class="small text-body text-center" v-html="item.display_date"></div>
              </template>

              <!-- Actions -->
              <template #actions="{ item }">
                <div class="text-center">
                  <BDropdown variant="link" no-caret toggle-class="p-0 border-0" 
                            menu-class="shadow-lg border-0" placement="bottom-end" offset="5" teleport-to="body">
                    <template #button-content>
                      <div class="action-btn">
                          <i class="ri-more-fill"></i>
                      </div>
                    </template>
                    <BDropdownItem @click="openEditModal(item)">
                      <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Chỉnh sửa
                    </BDropdownItem>
                    <div class="dropdown-divider"></div>
                    <BDropdownItem variant="danger" @click="deleteCategory(item.id)" class="text-danger">
                      <i class="ri-delete-bin-fill align-bottom me-2 text-danger"></i> Xóa
                    </BDropdownItem>
                  </BDropdown>
                </div>
              </template>

              <!-- Footer -->
              <template #footer>
                <div class="p-3">
                  <BRow class="align-items-center g-3 text-center text-sm-start">
                    <BCol sm>
                      <div class="text-muted small">
                        Hiển thị <span class="fw-semibold">{{ enrichedCategories.length }}</span> danh mục
                      </div>
                    </BCol>
                    <BCol sm="auto">
                      <div class="pagination-wrap">
                          <BPagination v-model="currentPage" :total-rows="enrichedCategories.length" :per-page="10" 
                                      class="mb-0" size="sm" hide-goto-end-buttons pills />
                      </div>
                    </BCol>
                  </BRow>
                </div>
              </template>
            </DTable>
          </BCardBody>
        </BCard>
      </BCol>
    </BRow>

    <!-- Category Form Modal -->
    <CategoryFormModal 
      v-model="showModal" 
      :editing-category="editingCategory"
      @save="saveCategory"
    />
  </div>
</template>

<style scoped>
/* Tab Styling */
.nav-tabs-custom {
    border: none;
}

.nav-tabs-custom .nav-link {
    border: none;
    font-weight: 500;
    color: var(--vz-secondary-color);
    position: relative;
    padding: 0.75rem 1rem;
    font-size: 14px;
}

.nav-tabs-custom .nav-link.active {
    color: var(--vz-primary);
    background-color: transparent;
}

.nav-tabs-custom .nav-link.active::after {
    content: "";
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 2px;
    background-color: var(--vz-primary);
}

/* Category Image Thumbnails */
.product-img-wrapper {
  width: 40px;
  height: 40px;
  background-color: var(--vz-light);
  border-radius: 6px;
  overflow: hidden;
  border: 1px solid var(--vz-border-color);
  display: flex;
  align-items: center;
  justify-content: center;
}

.product-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
</style>
