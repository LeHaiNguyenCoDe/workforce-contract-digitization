/**
 * useReturns Composable - Business logic
 */
import { ref, computed, onMounted, watch } from 'vue'
import { useSwal, useErrorHandler } from '@/utils'
import { erpReturnService } from '../services/returnService'
import { adminOrderService } from '@/plugins/api/services/OrderService'
import type { Return, ReturnStatus } from '../models/return'

export function useReturns() {
    const swal = useSwal()
    const { handleError } = useErrorHandler()

    // State
    const returns = ref<Return[]>([])
    const isLoading = ref(false)
    const isSaving = ref(false)
    const currentPage = ref(1)
    const totalPages = ref(1)
    const searchQuery = ref('')
    const statusFilter = ref<ReturnStatus | ''>('')
    const availableOrders = ref<any[]>([])

    // Modals
    const showDetailModal = ref(false)
    const showCreateModal = ref(false)
    const selectedReturn = ref<Return | null>(null)
    const selectedOrder = ref<any | null>(null)

    // Form
    const form = ref<any>({
        order_id: '',
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
            returns.value = response?.items || []
            totalPages.value = response?.meta?.last_page || 1
            currentPage.value = response?.meta?.current_page || 1
        } catch (error) {
            handleError(error, 'Không thể tải danh sách phiếu trả hàng')
            returns.value = []
        } finally {
            isLoading.value = false
        }
    }

    async function fetchOrderDetails(orderId: number) {
        try {
            const order = await adminOrderService.getById(orderId)
            selectedOrder.value = order
            // Default to all items being returned
            form.value.items = order.items?.map((item: any) => ({
                order_item_id: item.id,
                product_id: item.product_id,
                quantity: item.quantity,
                reason: ''
            })) || []
        } catch (error) {
            handleError(error, 'Không thể tải chi tiết đơn hàng')
        }
    }

    async function fetchAvailableOrders() {
        try {
            // Fetch delivered or completed orders for return
            const response = await adminOrderService.getAll({ 
                per_page: 100,
                status: 'delivered' // Or maybe also 'completed'
            })
            availableOrders.value = (response as any).items || []
        } catch (error) {
            handleError(error, 'Không thể tải danh sách đơn hàng khả dụng')
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
        form.value = { order_id: '', type: 'return', reason: '', notes: '', items: [] }
        selectedOrder.value = null
        fetchAvailableOrders()
        showCreateModal.value = true
    }

    async function handleCreate() {
        if (!form.value.order_id || !form.value.reason || !form.value.type || form.value.items.length === 0) {
            await swal.error('Vui lòng chọn đơn hàng, loại hình và ít nhất một sản phẩm.')
            return
        }

        isSaving.value = true
        try {
            await erpReturnService.create({
                ...form.value,
                order_id: Number(form.value.order_id),
                customer_id: selectedOrder.value?.customer_id
            })
            await swal.success('Đã tạo phiếu trả hàng thành công!')
            showCreateModal.value = false
            fetchReturns()
        } catch (error) {
            handleError(error, 'Không thể tạo phiếu trả hàng')
        } finally {
            isSaving.value = false
        }
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
            handleError(error, 'Không thể duyệt phiếu trả hàng')
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
            handleError(error, 'Không thể từ chối phiếu trả hàng')
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
            handleError(error, 'Không thể hoàn thành phiếu trả hàng')
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

    // Watches
    watch(() => form.value.order_id, (newOrderId) => {
        if (newOrderId) {
            fetchOrderDetails(Number(newOrderId))
        } else {
            selectedOrder.value = null
            form.value.items = []
        }
    })

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
        availableOrders,
        selectedOrder,
        // Modals
        showDetailModal,
        showCreateModal,
        selectedReturn,
        form,
        // Computed
        filteredReturns,
        // Methods
        fetchReturns,
        fetchAvailableOrders,
        fetchOrderDetails,
        openDetail,
        closeDetail,
        openCreate,
        handleCreate,
        handleApprove,
        handleReject,
        handleComplete,
        setSearch,
        setStatusFilter,
        changePage
    }
}
