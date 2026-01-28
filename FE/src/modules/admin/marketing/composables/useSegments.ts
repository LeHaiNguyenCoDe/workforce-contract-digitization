/**
 * useSegments Composable - Customer segmentation state and operations
 */
import { ref, computed } from 'vue'
import { useSwal, useErrorHandler } from '@/utils'
import segmentService from '../services/segmentService'
import type { CustomerSegment, SegmentStats, SegmentFilters, SegmentForm, SegmentCustomer } from '../models/segment'

export function useSegments() {
    const swal = useSwal()
    const { handleError } = useErrorHandler()

    // State
    const segments = ref<CustomerSegment[]>([])
    const stats = ref<SegmentStats>({
        total_segments: 0,
        dynamic_segments: 0,
        static_segments: 0,
        average_segment_size: 0,
        largest_segment: { name: '', size: 0 }
    })
    const selectedSegment = ref<CustomerSegment | null>(null)
    const segmentCustomers = ref<SegmentCustomer[]>([])
    const isLoading = ref(false)
    const isSaving = ref(false)

    // Pagination
    const currentPage = ref(1)
    const lastPage = ref(1)
    const total = ref(0)
    const perPage = ref(20)

    // Filters
    const filters = ref<SegmentFilters>({
        search: '',
        type: ''
    })

    // Form
    const form = ref<SegmentForm>({
        name: '',
        description: '',
        type: 'static',
        color: '#3B82F6',
        conditions: ''
    })

    // Modals
    const showCreateModal = ref(false)
    const showViewModal = ref(false)
    const editingSegment = ref<CustomerSegment | null>(null)

    // Computed
    const hasSegments = computed(() => segments.value.length > 0)

    /**
     * Fetch segments with current filters
     */
    async function fetchSegments(page?: number) {
        isLoading.value = true
        try {
            const result = await segmentService.getAll(
                filters.value,
                page || currentPage.value,
                perPage.value
            )
            segments.value = [...result.data]
            currentPage.value = result.current_page
            lastPage.value = result.last_page
            total.value = result.total
        } catch (error) {
            handleError(error, 'Không thể tải danh sách phân khúc')
            segments.value = []
        } finally {
            isLoading.value = false
        }
    }

    /**
     * Fetch segment statistics
     */
    async function fetchStats() {
        try {
            const result = await segmentService.getStats()
            stats.value = { ...result }
        } catch (error) {
            console.error('Error fetching stats:', error)
        }
    }

    /**
     * Create or update segment
     */
    async function saveSegment() {
        if (!form.value.name?.trim()) {
            await swal.warning('Vui lòng nhập tên phân khúc')
            return false
        }

        // Validate JSON conditions if provided
        if (form.value.type === 'dynamic' && form.value.conditions) {
            try {
                JSON.parse(form.value.conditions)
            } catch {
                await swal.warning('Điều kiện không phải JSON hợp lệ. Vui lòng kiểm tra lại.')
                return false
            }
        }

        isSaving.value = true
        try {
            if (editingSegment.value) {
                await segmentService.update(editingSegment.value.id, form.value)
                await swal.success('Cập nhật phân khúc thành công!')
            } else {
                await segmentService.create(form.value)
                await swal.success('Tạo phân khúc mới thành công!')
            }

            closeModal()
            await fetchSegments()
            await fetchStats()
            return true
        } catch (error) {
            handleError(error, 'Không thể lưu phân khúc')
            return false
        } finally {
            isSaving.value = false
        }
    }

    /**
     * Delete segment
     */
    async function deleteSegment(id: number) {
        const result = await swal.confirm('Bạn chắc chắn muốn xóa phân khúc này?')
        if (!result.isConfirmed) return

        try {
            await segmentService.delete(id)
            await swal.success('Xóa phân khúc thành công!')
            await fetchSegments()
            await fetchStats()
        } catch (error) {
            handleError(error, 'Không thể xóa phân khúc')
        }
    }

    /**
     * View segment customers
     */
    async function viewSegment(segment: CustomerSegment) {
        selectedSegment.value = segment
        try {
            const result = await segmentService.getCustomers(segment.id)
            segmentCustomers.value = [...result.data]
            showViewModal.value = true
        } catch (error) {
            handleError(error, 'Không thể tải danh sách khách hàng')
        }
    }

    /**
     * Open edit modal
     */
    function editSegment(segment: CustomerSegment) {
        editingSegment.value = segment
        form.value = {
            name: segment.name,
            description: segment.description || '',
            type: segment.type,
            color: segment.color || '#3B82F6',
            conditions: segment.conditions ? JSON.stringify(segment.conditions, null, 2) : ''
        }
        showCreateModal.value = true
    }

    /**
     * Open create modal
     */
    function openCreateModal() {
        resetForm()
        editingSegment.value = null
        showCreateModal.value = true
    }

    /**
     * Close modals and reset form
     */
    function closeModal() {
        showCreateModal.value = false
        showViewModal.value = false
        editingSegment.value = null
        selectedSegment.value = null
        segmentCustomers.value = []
        resetForm()
    }

    /**
     * Reset form to initial state
     */
    function resetForm() {
        form.value = {
            name: '',
            description: '',
            type: 'static',
            color: '#3B82F6',
            conditions: ''
        }
    }

    /**
     * Recalculate dynamic segment
     */
    async function recalculateSegment(segmentId: number) {
        try {
            await segmentService.calculate(segmentId)
            await swal.success('Đã cập nhật phân khúc!')
            await fetchSegments()
        } catch (error) {
            handleError(error, 'Không thể cập nhật phân khúc')
        }
    }

    /**
     * Change page
     */
    function changePage(page: number) {
        currentPage.value = page
        fetchSegments(page)
    }

    /**
     * Apply filters and fetch
     */
    function applyFilters() {
        currentPage.value = 1
        fetchSegments(1)
    }

    /**
     * Reset filters
     */
    function resetFilters() {
        filters.value = {
            search: '',
            type: ''
        }
        currentPage.value = 1
        fetchSegments(1)
    }

    return {
        // State
        segments,
        stats,
        selectedSegment,
        segmentCustomers,
        isLoading,
        isSaving,
        hasSegments,

        // Pagination
        currentPage,
        lastPage,
        total,
        perPage,

        // Filters
        filters,

        // Form
        form,
        editingSegment,

        // Modals
        showCreateModal,
        showViewModal,

        // Methods
        fetchSegments,
        fetchStats,
        saveSegment,
        deleteSegment,
        viewSegment,
        editSegment,
        openCreateModal,
        closeModal,
        resetForm,
        recalculateSegment,
        changePage,
        applyFilters,
        resetFilters
    }
}
