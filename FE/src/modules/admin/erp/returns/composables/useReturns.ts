/**
 * useReturns Composable - Business logic
 */
import { ref, computed, onMounted } from 'vue'
import { useSwal } from '@/shared/utils'
import { erpReturnService } from '../services/returnService'
import type { Return, ReturnStatus, ReturnFilters, CreateReturnPayload } from '../models/return'

// Mock data for development
const getMockReturns = (): Return[] => [
    {
        id: 1,
        order_id: 1001,
        customer_id: 5,
        status: 'pending',
        reason: 'Sản phẩm bị lỗi',
        notes: 'Khách hàng phản ánh sản phẩm không hoạt động',
        refund_amount: 500000,
        created_at: '2024-12-20T10:00:00Z',
        updated_at: '2024-12-20T10:00:00Z',
        customer: { id: 5, name: 'Nguyễn Văn A', email: 'a@example.com' },
        order: { id: 1001, total_amount: 500000 }
    },
    {
        id: 2,
        order_id: 1002,
        customer_id: 8,
        status: 'approved',
        reason: 'Đổi size',
        refund_amount: 0,
        created_at: '2024-12-21T14:30:00Z',
        updated_at: '2024-12-22T09:00:00Z',
        customer: { id: 8, name: 'Trần Thị B', email: 'b@example.com' },
        order: { id: 1002, total_amount: 350000 }
    },
    {
        id: 3,
        order_id: 1003,
        customer_id: 12,
        status: 'completed',
        reason: 'Không đúng mô tả',
        refund_amount: 750000,
        refund_method: 'original',
        created_at: '2024-12-15T08:00:00Z',
        updated_at: '2024-12-18T16:00:00Z',
        customer: { id: 12, name: 'Lê Văn C', email: 'c@example.com' },
        order: { id: 1003, total_amount: 750000 }
    }
]

export function useReturns() {
    const swal = useSwal()

    // State
    const returns = ref<Return[]>([])
    const isLoading = ref(false)
    const isSaving = ref(false)
    const currentPage = ref(1)
    const totalPages = ref(1)
    const searchQuery = ref('')
    const statusFilter = ref<ReturnStatus | ''>('')

    // Modals
    const showDetailModal = ref(false)
    const showCreateModal = ref(false)
    const selectedReturn = ref<Return | null>(null)

    // Form
    const form = ref<CreateReturnPayload>({
        order_id: 0,
        reason: '',
        notes: ''
    })

    // Computed
    const filteredReturns = computed(() => {
        if (!searchQuery.value) return returns.value
        const query = searchQuery.value.toLowerCase()
        return returns.value.filter(r =>
            r.id.toString().includes(query) ||
            r.order_id.toString().includes(query) ||
            r.reason.toLowerCase().includes(query)
        )
    })

    // Methods
    async function fetchReturns() {
        isLoading.value = true
        try {
            const params: Record<string, any> = {
                page: currentPage.value,
                per_page: 15
            }
            if (statusFilter.value) params.status = statusFilter.value
            if (searchQuery.value) params.search = searchQuery.value

            const response = await erpReturnService.getAll(params)
            returns.value = response.data
            totalPages.value = response.last_page
        } catch (error) {
            console.error('Failed to fetch returns:', error)
            returns.value = getMockReturns()
        } finally {
            isLoading.value = false
        }
    }

    function openDetail(item: Return) {
        selectedReturn.value = item
        showDetailModal.value = true
    }

    function closeDetail() {
        selectedReturn.value = null
        showDetailModal.value = false
    }

    function openCreate() {
        form.value = { order_id: 0, reason: '', notes: '' }
        showCreateModal.value = true
    }

    async function handleApprove(id: number) {
        const confirmed = await swal.confirm('Xác nhận duyệt phiếu trả hàng này?')
        if (!confirmed) return

        isSaving.value = true
        try {
            await erpReturnService.approve(id)
            await swal.success('Đã duyệt phiếu trả hàng!')
            await fetchReturns()
        } catch (error) {
            await swal.error('Không thể duyệt phiếu trả hàng')
        } finally {
            isSaving.value = false
        }
    }

    async function handleReject(id: number) {
        const confirmed = await swal.confirm('Xác nhận từ chối phiếu trả hàng này?')
        if (!confirmed) return

        isSaving.value = true
        try {
            await erpReturnService.reject(id, 'Từ chối bởi admin')
            await swal.success('Đã từ chối phiếu trả hàng!')
            await fetchReturns()
        } catch (error) {
            await swal.error('Không thể từ chối phiếu trả hàng')
        } finally {
            isSaving.value = false
        }
    }

    async function handleComplete(id: number) {
        const confirmed = await swal.confirm('Xác nhận hoàn thành phiếu trả hàng?')
        if (!confirmed) return

        isSaving.value = true
        try {
            await erpReturnService.complete(id, 'original')
            await swal.success('Đã hoàn thành phiếu trả hàng!')
            await fetchReturns()
        } catch (error) {
            await swal.error('Không thể hoàn thành phiếu trả hàng')
        } finally {
            isSaving.value = false
        }
    }

    function setSearch(query: string) {
        searchQuery.value = query
        currentPage.value = 1
        fetchReturns()
    }

    function setStatusFilter(status: ReturnStatus | '') {
        statusFilter.value = status
        currentPage.value = 1
        fetchReturns()
    }

    function changePage(page: number) {
        currentPage.value = page
        fetchReturns()
    }

    // Initialize
    onMounted(fetchReturns)

    return {
        // State
        returns,
        isLoading,
        isSaving,
        currentPage,
        totalPages,
        searchQuery,
        statusFilter,
        // Modals
        showDetailModal,
        showCreateModal,
        selectedReturn,
        form,
        // Computed
        filteredReturns,
        // Methods
        fetchReturns,
        openDetail,
        closeDetail,
        openCreate,
        handleApprove,
        handleReject,
        handleComplete,
        setSearch,
        setStatusFilter,
        changePage
    }
}
