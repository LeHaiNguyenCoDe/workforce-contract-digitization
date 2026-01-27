/**
 * CRUD Factory Composable
 * Provides standardized CRUD operations for admin modules
 * Reduces code duplication across useExpenses, useCustomers, usePoints, etc.
 */
import { ref, computed, onMounted, type Ref, type ComputedRef } from 'vue'
import { useSwal } from '@/utils'
import { useErrorHandler } from '@/utils/useErrorHandler'

// ============================================
// Types
// ============================================

export interface PaginatedResponse<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}

export interface CRUDService<T, CreatePayload = Partial<T>, UpdatePayload = Partial<T>> {
  getAll: (params?: Record<string, unknown>) => Promise<T[] | PaginatedResponse<T>>
  getById?: (id: number) => Promise<T>
  create: (data: CreatePayload) => Promise<T>
  update: (id: number, data: UpdatePayload) => Promise<T>
  delete: (id: number) => Promise<void>
}

export interface CRUDOptions<T, CreatePayload> {
  /** Service containing API methods */
  service: CRUDService<T, CreatePayload>
  /** Default form values for create */
  defaultForm: CreatePayload
  /** Field to use for delete confirmation message */
  deleteConfirmField?: keyof T
  /** Success messages */
  messages?: {
    createSuccess?: string
    updateSuccess?: string
    deleteSuccess?: string
    deleteConfirm?: string
  }
  /** Auto-fetch on mount */
  fetchOnMount?: boolean
  /** Items per page for pagination */
  perPage?: number
}

export interface CRUDState<T, CreatePayload> {
  items: Ref<T[]>
  isLoading: Ref<boolean>
  isSaving: Ref<boolean>
  showModal: Ref<boolean>
  editingItem: Ref<T | null>
  form: Ref<CreatePayload>
  searchQuery: Ref<string>
  currentPage: Ref<number>
  totalPages: Ref<number>
  total: Ref<number>
}

export interface CRUDActions<T, CreatePayload> {
  fetch: (params?: Record<string, unknown>) => Promise<void>
  openCreate: () => void
  openEdit: (item: T) => void
  closeModal: () => void
  save: () => Promise<boolean>
  deleteItem: (item: T) => Promise<boolean>
  setSearch: (query: string) => void
  changePage: (page: number) => void
  resetForm: () => void
}

export interface CRUDComposable<T, CreatePayload> extends CRUDState<T, CreatePayload>, CRUDActions<T, CreatePayload> {
  filteredItems: ComputedRef<T[]>
}

// ============================================
// Main Factory
// ============================================

/**
 * Create a CRUD composable with standardized state and actions
 *
 * @example
 * ```ts
 * const expenseService = {
 *   getAll: (params) => httpClient.get('/expenses', { params }).then(r => r.data),
 *   create: (data) => httpClient.post('/expenses', data).then(r => r.data),
 *   update: (id, data) => httpClient.put(`/expenses/${id}`, data).then(r => r.data),
 *   delete: (id) => httpClient.delete(`/expenses/${id}`)
 * }
 *
 * export function useExpenses() {
 *   const crud = useCRUD({
 *     service: expenseService,
 *     defaultForm: { amount: 0, category_id: null },
 *     deleteConfirmField: 'expense_code',
 *     messages: { createSuccess: 'Tạo chi phí thành công!' }
 *   })
 *
 *   // Add custom logic
 *   const summary = ref({ total: 0 })
 *
 *   return { ...crud, summary }
 * }
 * ```
 */
export function useCRUD<T extends { id: number }, CreatePayload = Partial<T>>(
  options: CRUDOptions<T, CreatePayload>
): CRUDComposable<T, CreatePayload> {
  const {
    service,
    defaultForm,
    deleteConfirmField = 'id' as keyof T,
    messages = {},
    fetchOnMount = true,
    perPage = 15
  } = options

  const swal = useSwal()
  const { handleError } = useErrorHandler()

  // ============================================
  // State
  // ============================================

  const items = ref<T[]>([]) as Ref<T[]>
  const isLoading = ref(false)
  const isSaving = ref(false)
  const showModal = ref(false)
  const editingItem = ref<T | null>(null) as Ref<T | null>
  const form = ref<CreatePayload>({ ...defaultForm }) as Ref<CreatePayload>
  const searchQuery = ref('')
  const currentPage = ref(1)
  const totalPages = ref(1)
  const total = ref(0)

  // ============================================
  // Computed
  // ============================================

  /**
   * Client-side filtered items based on search query
   * Override this in specific composables for custom filtering
   */
  const filteredItems = computed<T[]>(() => {
    if (!searchQuery.value) return items.value

    const query = searchQuery.value.toLowerCase()
    return items.value.filter(item => {
      // Search in all string fields
      return Object.values(item as Record<string, unknown>).some(value => {
        if (typeof value === 'string') {
          return value.toLowerCase().includes(query)
        }
        if (typeof value === 'number') {
          return String(value).includes(query)
        }
        return false
      })
    })
  })

  // ============================================
  // Actions
  // ============================================

  /**
   * Fetch items from the server
   */
  async function fetch(params: Record<string, unknown> = {}): Promise<void> {
    isLoading.value = true
    try {
      const response = await service.getAll({
        page: currentPage.value,
        per_page: perPage,
        search: searchQuery.value || undefined,
        ...params
      })

      // Handle both array and paginated responses
      if (Array.isArray(response)) {
        items.value = response
        totalPages.value = 1
        total.value = response.length
      } else {
        items.value = response.data
        totalPages.value = response.last_page
        total.value = response.total
        currentPage.value = response.current_page
      }
    } catch (error) {
      await handleError(error, 'Không thể tải dữ liệu')
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Open modal for creating new item
   */
  function openCreate(): void {
    editingItem.value = null
    form.value = { ...defaultForm }
    showModal.value = true
  }

  /**
   * Open modal for editing existing item
   */
  function openEdit(item: T): void {
    editingItem.value = item
    // Copy item fields to form
    form.value = { ...defaultForm }
    for (const key of Object.keys(defaultForm as object)) {
      const k = key as keyof CreatePayload
      if (key in item) {
        (form.value as Record<string, unknown>)[key] = (item as Record<string, unknown>)[key]
      }
    }
    showModal.value = true
  }

  /**
   * Close modal and reset form
   */
  function closeModal(): void {
    showModal.value = false
    editingItem.value = null
    form.value = { ...defaultForm }
  }

  /**
   * Reset form to default values
   */
  function resetForm(): void {
    form.value = { ...defaultForm }
  }

  /**
   * Save item (create or update)
   */
  async function save(): Promise<boolean> {
    isSaving.value = true
    try {
      if (editingItem.value) {
        await service.update(editingItem.value.id, form.value)
        await swal.success(messages.updateSuccess || 'Cập nhật thành công!')
      } else {
        await service.create(form.value)
        await swal.success(messages.createSuccess || 'Tạo mới thành công!')
      }
      closeModal()
      await fetch()
      return true
    } catch (error) {
      await handleError(error)
      return false
    } finally {
      isSaving.value = false
    }
  }

  /**
   * Delete item with confirmation
   */
  async function deleteItem(item: T): Promise<boolean> {
    const identifier = String(item[deleteConfirmField])
    const confirmMessage = messages.deleteConfirm || `Xóa ${identifier}?`

    const confirmed = await swal.confirm(confirmMessage)
    if (!confirmed) return false

    try {
      await service.delete(item.id)
      await swal.success(messages.deleteSuccess || 'Đã xóa!')
      await fetch()
      return true
    } catch (error) {
      await handleError(error)
      return false
    }
  }

  /**
   * Set search query and reset to page 1
   */
  function setSearch(query: string): void {
    searchQuery.value = query
    currentPage.value = 1
    fetch()
  }

  /**
   * Change page
   */
  function changePage(page: number): void {
    currentPage.value = page
    fetch()
  }

  // ============================================
  // Lifecycle
  // ============================================

  if (fetchOnMount) {
    onMounted(() => {
      fetch()
    })
  }

  // ============================================
  // Return
  // ============================================

  return {
    // State
    items,
    isLoading,
    isSaving,
    showModal,
    editingItem,
    form,
    searchQuery,
    currentPage,
    totalPages,
    total,

    // Computed
    filteredItems,

    // Actions
    fetch,
    openCreate,
    openEdit,
    closeModal,
    save,
    deleteItem,
    setSearch,
    changePage,
    resetForm
  }
}

// ============================================
// Read-Only CRUD (for listing without create/edit)
// ============================================

export interface ReadOnlyService<T> {
  getAll: (params?: Record<string, unknown>) => Promise<T[] | PaginatedResponse<T>>
  getById?: (id: number) => Promise<T>
}

export interface ReadOnlyOptions<T> {
  service: ReadOnlyService<T>
  fetchOnMount?: boolean
  perPage?: number
}

/**
 * Read-only list composable for display-only modules
 */
export function useReadOnlyList<T extends { id: number }>(
  options: ReadOnlyOptions<T>
) {
  const { service, fetchOnMount = true, perPage = 15 } = options
  const { handleError } = useErrorHandler()

  const items = ref<T[]>([]) as Ref<T[]>
  const isLoading = ref(false)
  const searchQuery = ref('')
  const currentPage = ref(1)
  const totalPages = ref(1)
  const total = ref(0)

  const filteredItems = computed<T[]>(() => {
    if (!searchQuery.value) return items.value
    const query = searchQuery.value.toLowerCase()
    return items.value.filter(item => {
      return Object.values(item as Record<string, unknown>).some(value => {
        if (typeof value === 'string') return value.toLowerCase().includes(query)
        if (typeof value === 'number') return String(value).includes(query)
        return false
      })
    })
  })

  async function fetch(params: Record<string, unknown> = {}): Promise<void> {
    isLoading.value = true
    try {
      const response = await service.getAll({
        page: currentPage.value,
        per_page: perPage,
        search: searchQuery.value || undefined,
        ...params
      })

      if (Array.isArray(response)) {
        items.value = response
        totalPages.value = 1
        total.value = response.length
      } else {
        items.value = response.data
        totalPages.value = response.last_page
        total.value = response.total
      }
    } catch (error) {
      await handleError(error, 'Không thể tải dữ liệu')
    } finally {
      isLoading.value = false
    }
  }

  function setSearch(query: string): void {
    searchQuery.value = query
    currentPage.value = 1
    fetch()
  }

  function changePage(page: number): void {
    currentPage.value = page
    fetch()
  }

  if (fetchOnMount) {
    onMounted(() => fetch())
  }

  return {
    items,
    isLoading,
    searchQuery,
    currentPage,
    totalPages,
    total,
    filteredItems,
    fetch,
    setSearch,
    changePage
  }
}
