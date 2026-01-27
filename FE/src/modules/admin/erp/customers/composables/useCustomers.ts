/**
 * useCustomers Composable
 */
import { ref, computed, onMounted } from 'vue'
import customerService from '../services/customerService'
import type { Customer } from '../models/customer'

const getMockCustomers = (): Customer[] => [
    { id: 1, name: 'Nguyễn Văn An', email: 'an@email.com', phone: '0901234567', role: 'customer', active: true, total_orders: 15, total_spent: 12500000, created_at: '2024-01-15' },
    { id: 2, name: 'Trần Thị Bình', email: 'binh@email.com', phone: '0912345678', role: 'customer', active: true, total_orders: 8, total_spent: 5600000, created_at: '2024-03-20' },
    { id: 3, name: 'Lê Hoàng Cường', email: 'cuong@email.com', phone: '0923456789', role: 'customer', active: true, total_orders: 3, total_spent: 2100000, created_at: '2024-06-10' },
    { id: 4, name: 'Phạm Thu Dung', email: 'dung@email.com', phone: '0934567890', role: 'customer', active: false, total_orders: 1, total_spent: 350000, created_at: '2024-08-05' },
    { id: 5, name: 'Hoàng Minh Đức', email: 'duc@email.com', phone: '0945678901', role: 'customer', active: true, total_orders: 25, total_spent: 28900000, created_at: '2023-11-20' }
]

export function useCustomers() {
    const customers = ref<Customer[]>([])
    const isLoading = ref(false)
    const currentPage = ref(1)
    const totalPages = ref(1)
    const searchQuery = ref('')

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
            customers.value = response.data
            totalPages.value = response.last_page
        } catch (error) {
            console.error('Failed to fetch customers:', error)
            customers.value = getMockCustomers()
        } finally {
            isLoading.value = false
        }
    }

    function openDetail(customer: Customer) {
        selectedCustomer.value = customer
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
