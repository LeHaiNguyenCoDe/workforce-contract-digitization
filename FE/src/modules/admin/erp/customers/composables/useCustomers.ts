/**
 * useCustomers Composable
 */
import { ref, computed, onMounted } from 'vue'
import customerService from '../services/customerService'
import type { Customer } from '../models/customer'
import { useErrorHandler } from '@/utils'

export function useCustomers() {
    const customers = ref<Customer[]>([])
    const isLoading = ref(false)
    const currentPage = ref(1)
    const totalPages = ref(1)
    const searchQuery = ref('')
    const { handleError } = useErrorHandler()

    const showDetailModal = ref(false)
    const selectedCustomer = ref<Customer | null>(null)
    const customerOrders = ref<any[]>([])

    const filteredCustomers = computed(() => {
        if (!searchQuery.value) return customers.value
        const query = searchQuery.value.toLowerCase()
        return customers.value.filter(c =>
            c.name.toLowerCase().includes(query) ||
            c.email.toLowerCase().includes(query) ||
            c.phone?.includes(query)
        )
    })

    const stats = computed(() => ({
        total: customers.value.length,
        active: customers.value.filter(c => c.active).length,
        totalSpent: customers.value.reduce((sum, c) => sum + (c.total_spent || 0), 0)
    }))

    async function fetchCustomers() {
        isLoading.value = true
        try {
            const response = await customerService.getAll({
                search: searchQuery.value || undefined,
                page: currentPage.value,
                per_page: 15
            })
            customers.value = response.items
            totalPages.value = response.meta.last_page
            currentPage.value = response.meta.current_page
        } catch (error) {
            handleError(error, 'Không thể tải danh sách khách hàng')
            customers.value = []
        } finally {
            isLoading.value = false
        }
    }

    function openDetail(customer: Customer) {
        selectedCustomer.value = customer
        // In a real app, these should also be fetched from API
        customerOrders.value = [
            { id: 1001, total_amount: 500000, status: 'delivered', created_at: '2024-12-20' },
            { id: 1002, total_amount: 350000, status: 'processing', created_at: '2024-12-22' }
        ]
        showDetailModal.value = true
    }

    function closeDetail() {
        selectedCustomer.value = null
        showDetailModal.value = false
    }

    function setSearch(query: string) {
        searchQuery.value = query
        currentPage.value = 1
        fetchCustomers()
    }

    function changePage(page: number) {
        currentPage.value = page
        fetchCustomers()
    }

    onMounted(fetchCustomers)

    return {
        customers,
        isLoading,
        currentPage,
        totalPages,
        searchQuery,
        showDetailModal,
        selectedCustomer,
        customerOrders,
        filteredCustomers,
        stats,
        fetchCustomers,
        openDetail,
        closeDetail,
        setSearch,
        changePage
    }
}
