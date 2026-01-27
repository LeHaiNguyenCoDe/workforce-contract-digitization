/**
 * Composable for common admin list view logic
 */
import { ref, computed } from 'vue'
import { usePagination } from './usePagination'

export interface UseAdminListOptions<T> {
    fetchFn: (params: any) => Promise<{ data: T[]; last_page?: number; total?: number }>
    perPage?: number
    searchFields?: string[]
}

export function useAdminList<T extends { id: number | string }>(options: UseAdminListOptions<T>) {
    const { fetchFn, perPage = 15, searchFields = [] } = options

    const items = ref<T[]>([]) as ReturnType<typeof ref<T[]>>
    const isLoading = ref(false)
    const searchQuery = ref('')
    const error = ref<string | null>(null)

    const pagination = usePagination({ perPage })

    const filteredItems = computed(() => {
        if (!searchQuery.value || searchFields.length === 0) {
            return items.value
        }

        const query = searchQuery.value.toLowerCase()
        return items.value.filter(item => {
            return searchFields.some(field => {
                const value = (item as any)[field]
                if (value === undefined || value === null) return false
                return String(value).toLowerCase().includes(query)
            })
        })
    })

    const isEmpty = computed(() => items.value.length === 0)
    const hasItems = computed(() => items.value.length > 0)

    async function fetchItems(params: Record<string, any> = {}) {
        isLoading.value = true
        error.value = null

        try {
            const fetchParams = {
                page: pagination.currentPage.value,
                per_page: pagination.perPage.value,
                search: searchQuery.value || undefined,
                ...params
            }

            const response = await fetchFn(fetchParams)

            items.value = response.data as T[]

            if (response.last_page !== undefined) {
                pagination.setTotalPages(response.last_page)
            }
            if (response.total !== undefined) {
                pagination.setTotalItems(response.total)
            }
        } catch (e: any) {
            console.error('Failed to fetch items:', e)
            error.value = e.message || 'Có lỗi xảy ra'
            items.value = []
        } finally {
            isLoading.value = false
        }
    }

    function setSearch(query: string) {
        searchQuery.value = query
        pagination.setPage(1)
    }

    function changePage(page: number) {
        pagination.setPage(page)
        fetchItems()
    }

    function refresh() {
        fetchItems()
    }

    function reset() {
        items.value = []
        searchQuery.value = ''
        error.value = null
        pagination.reset()
    }

    return {
        items,
        isLoading,
        searchQuery,
        error,
        filteredItems,
        isEmpty,
        hasItems,
        currentPage: pagination.currentPage,
        totalPages: pagination.totalPages,
        totalItems: pagination.totalItems,
        hasNextPage: pagination.hasNextPage,
        hasPrevPage: pagination.hasPrevPage,
        fetchItems,
        setSearch,
        changePage,
        refresh,
        reset
    }
}
