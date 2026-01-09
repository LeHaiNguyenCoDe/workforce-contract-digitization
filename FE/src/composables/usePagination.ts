/**
 * Composable for pagination logic
 */
import { ref, computed } from 'vue'

export interface UsePaginationOptions {
    initialPage?: number
    perPage?: number
}

export function usePagination(options: UsePaginationOptions = {}) {
    const currentPage = ref(options.initialPage || 1)
    const totalPages = ref(1)
    const totalItems = ref(0)
    const perPage = ref(options.perPage || 15)

    const hasNextPage = computed(() => currentPage.value < totalPages.value)
    const hasPrevPage = computed(() => currentPage.value > 1)

    const startItem = computed(() => {
        if (totalItems.value === 0) return 0
        return (currentPage.value - 1) * perPage.value + 1
    })

    const endItem = computed(() => {
        return Math.min(currentPage.value * perPage.value, totalItems.value)
    })

    function setPage(page: number) {
        if (page >= 1 && page <= totalPages.value) {
            currentPage.value = page
        }
    }

    function nextPage() {
        if (hasNextPage.value) {
            currentPage.value++
        }
    }

    function prevPage() {
        if (hasPrevPage.value) {
            currentPage.value--
        }
    }

    function setTotalPages(total: number) {
        totalPages.value = total
        if (currentPage.value > total && total > 0) {
            currentPage.value = total
        }
    }

    function setTotalItems(total: number) {
        totalItems.value = total
    }

    function reset() {
        currentPage.value = 1
        totalPages.value = 1
        totalItems.value = 0
    }

    return {
        currentPage,
        totalPages,
        totalItems,
        perPage,
        hasNextPage,
        hasPrevPage,
        startItem,
        endItem,
        setPage,
        nextPage,
        prevPage,
        setTotalPages,
        setTotalItems,
        reset
    }
}
