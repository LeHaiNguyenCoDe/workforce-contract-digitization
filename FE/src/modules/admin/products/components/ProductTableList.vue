<script setup lang="ts">
import { computed } from 'vue'
import { useAdminProductStore } from '../store/store'

const props = defineProps<{
  columns: any[]
  products: any[]
  isLoading: boolean
  formatPrice: (price: number) => string
}>()

const searchQuery = defineModel<string>('searchQuery', { default: '' })
const activeTab = defineModel<number>('activeTab', { default: 0 })
const currentPage = defineModel<number>('currentPage', { default: 1 })
const selectedRows = defineModel<number[]>('selectedRows', { default: () => [] })

const emit = defineEmits(['create', 'edit', 'view', 'delete', 'delete-bulk', 'change-page', 'search-input'])

const store = useAdminProductStore()

// Computed for selection info
const hasSelectedRows = computed(() => selectedRows.value.length > 0)

const handleSearchInput = (val: string) => {
  emit('search-input', val)
}

const handleCreate = () => {
  emit('create')
}

const handleEdit = (id: number) => {
  emit('edit', id)
}

const handleView = (id: number) => {
  emit('view', id)
}

const handleDelete = (id: number) => {
  emit('delete', id)
}

const handleChangePage = (val: number) => {
  emit('change-page', val)
}

const handleRemoveSelected = () => {
  // Emit bulk delete with all selected IDs
  const idsToDelete = [...selectedRows.value]
  emit('delete-bulk', idsToDelete)
  selectedRows.value = []
}
</script>

<template>
  <BCard no-body class="border-0 shadow-sm mb-4 overflow-hidden" style="border-radius: 8px;">
    <BCardBody class="p-3 pb-0">
      <BRow class="g-3 align-items-center mb-3">
        <BCol>
          <BButton variant="success" @click="handleCreate" class="d-flex align-items-center gap-2 px-3 fw-medium" style="background-color: #0ab39c; border-color: #0ab39c;">
            <i class="ri-add-line align-bottom me-1"></i> Add Product
          </BButton>
        </BCol>
        <BCol md="auto">
          <div class="search-box">
            <input type="text" class="form-control border-light" style="width: 250px;" v-model="searchQuery" 
                   placeholder="Search Products..." @input="handleSearchInput(searchQuery)">
            <i class="ri-search-line search-icon text-muted"></i>
          </div>
        </BCol>
      </BRow>

      <!-- Tabs with Selection Actions -->
      <div class="d-flex align-items-center justify-content-between border-bottom">
        <BNav tabs class="nav-tabs-custom border-0">
            <BNavItem :active="activeTab === 0" @click="activeTab = 0">
                All <BBadge variant="primary" class="bg-primary-subtle text-primary ms-1 fs-11 px-1 rounded-1">{{ store.products.length }}</BBadge>
            </BNavItem>
            <BNavItem :active="activeTab === 1" @click="activeTab = 1">
                Published <BBadge variant="danger" class="bg-danger-subtle text-danger ms-1 fs-11 px-1 rounded-1">{{ store.products.filter(p => p.is_active).length }}</BBadge>
            </BNavItem>
            <BNavItem :active="activeTab === 2" @click="activeTab = 2">
                Draft
            </BNavItem>
        </BNav>
        
        <!-- Selection Actions -->
        <div v-if="hasSelectedRows" class="d-flex align-items-center gap-3 pb-2">
          <span class="text-muted small">Select Result</span>
          <a href="javascript:void(0);" class="text-danger fw-medium small" @click="handleRemoveSelected">Remove</a>
        </div>
      </div>
    </BCardBody>

    <BCardBody class="p-0">
          <DTable 
            :columns="columns" 
            :data="products" 
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

            <!-- ID -->
            <template #cell-id="{ item }">
              <span class="text-muted">{{ item.id }}</span>
            </template>

            <!-- Product Name / Thumbnail -->
            <template #cell-name="{ item }">
              <div class="d-flex align-items-center">
                <div class="flex-shrink-0 me-3">
                  <div class="product-img-wrapper">
                    <img v-if="item.thumbnail" :src="item.thumbnail" :alt="item.name" class="product-img" />
                    <div v-else class="d-flex align-items-center justify-content-center" style="font-size: 22px;">📦</div>
                  </div>
                </div>
                <div class="flex-grow-1 overflow-hidden">
                  <h5 class="fs-14 mb-1 text-truncate">
                    <BLink @click="handleEdit(item.id)" class="text-dark fw-semibold text-decoration-none">{{ item.name }}</BLink>
                  </h5>
                  <p class="text-muted mb-0 small text-truncate">Category : <span class="text-primary">{{ item.category?.name || 'N/A' }}</span></p>
                </div>
              </div>
            </template>

            <!-- Stock -->
            <template #cell-stock_quantity="{ item }">
              <div class="text-body text-center">{{ item.stock_quantity }}</div>
            </template>

            <!-- Price -->
            <template #cell-price="{ item }">
              <div class="fw-medium text-primary text-center">{{ formatPrice(item.price) }}</div>
            </template>

            <!-- Orders -->
            <template #cell-orders_count="{ item }">
              <div class="text-primary text-center fw-medium">{{ item.orders_count }}</div>
            </template>

            <!-- Rating -->
            <template #cell-rating="{ item }">
              <div class="text-center">
                <span class="rating-display">
                  <i class="ri-star-fill text-warning me-1"></i>
                  <span class="text-body">{{ item.rating_val }}</span>
                </span>
              </div>
            </template>

            <!-- Published At -->
            <template #cell-published_at="{ item }">
              <div class="published-date text-center" v-html="item.display_date"></div>
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
                  <BDropdownItem @click="handleView(item.id)">
                    <i class="ri-eye-fill align-bottom me-2 text-muted"></i> View
                  </BDropdownItem>
                  <BDropdownItem @click="handleEdit(item.id)">
                    <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit
                  </BDropdownItem>
                  <div class="dropdown-divider"></div>
                  <BDropdownItem variant="danger" @click="handleDelete(item.id)" class="text-danger">
                    <i class="ri-delete-bin-fill align-bottom me-2 text-danger"></i> Delete
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
                      Showing <span class="fw-semibold">{{ (currentPage - 1) * 10 + 1 }} to {{ Math.min(currentPage * 10, products.length) }}</span> of {{ products.length }} results
                    </div>
                  </BCol>
                  <BCol sm="auto">
                    <div class="pagination-wrap">
                        <BPagination v-model="currentPage" :total-rows="products.length" :per-page="10" @update:model-value="(val) => handleChangePage(Number(val))" 
                                    class="mb-0" size="sm" hide-goto-end-buttons pills />
                    </div>
                  </BCol>
                </BRow>
              </div>
            </template>
          </DTable>
        </BCardBody>
      </BCard>
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
</style>
