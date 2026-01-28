/**
 * useCoupons Composable - Coupon management state and operations
 */
import { ref, computed } from 'vue'
import { useSwal, useErrorHandler } from '@/utils'
import couponService from '../services/couponService'
import type { Coupon, CouponStats, CouponFilters, CouponForm, CouponType } from '../models/coupon'

export function useCoupons() {
    const swal = useSwal()
    const { handleError } = useErrorHandler()

    // State
    const coupons = ref<Coupon[]>([])
    const stats = ref<CouponStats>({
        total_coupons: 0,
        active_coupons: 0,
        total_usages: 0,
        usage_rate: 0,
        total_discount_given: 0,
        by_type: {} as any
    })
    const selectedCoupon = ref<Coupon | null>(null)
    const isLoading = ref(false)
    const isSaving = ref(false)

    // Pagination
    const currentPage = ref(1)
    const lastPage = ref(1)
    const total = ref(0)
    const perPage = ref(20)

    // Filters
    const filters = ref<CouponFilters>({
        search: '',
        type: '',
        status: ''
    })

    // Form
    const form = ref<CouponForm>({
        code: '',
        name: '',
        description: '',
        type: 'percentage',
        value: 0,
        min_purchase_amount: null,
        max_discount_amount: null,
        usage_limit_total: null,
        usage_limit_per_user: null,
        usage_limit_per_day: null,
        auto_apply: false,
        stackable: false,
        first_order_only: false,
        valid_from: '',
        valid_to: ''
    })

    // Modals
    const showCreateModal = ref(false)
    const editingCoupon = ref<Coupon | null>(null)

    // Computed
    const hasCoupons = computed(() => coupons.value.length > 0)

    /**
     * Fetch coupons with current filters
     */
    async function fetchCoupons(page?: number) {
        isLoading.value = true
        try {
            const result = await couponService.getAll(
                filters.value,
                page || currentPage.value,
                perPage.value
            )
            coupons.value = [...result.data]
            currentPage.value = result.current_page
            lastPage.value = result.last_page
            total.value = result.total
        } catch (error) {
            handleError(error, 'Không thể tải danh sách mã giảm giá')
            coupons.value = []
        } finally {
            isLoading.value = false
        }
    }

    /**
     * Fetch coupon statistics
     */
    async function fetchStats() {
        try {
            const result = await couponService.getStats()
            stats.value = { ...result }
        } catch (error) {
            console.error('Error fetching stats:', error)
        }
    }

    /**
     * Create or update coupon
     */
    async function saveCoupon() {
        if (!form.value.code?.trim()) {
            await swal.warning('Vui lòng nhập mã coupon')
            return false
        }

        if (!form.value.name?.trim()) {
            await swal.warning('Vui lòng nhập tên coupon')
            return false
        }

        isSaving.value = true
        try {
            if (editingCoupon.value) {
                await couponService.update(editingCoupon.value.id, form.value)
                await swal.success('Cập nhật mã giảm giá thành công!')
            } else {
                await couponService.create(form.value)
                await swal.success('Tạo mã giảm giá mới thành công!')
            }

            closeModal()
            await fetchCoupons()
            await fetchStats()
            return true
        } catch (error) {
            handleError(error, 'Không thể lưu mã giảm giá')
            return false
        } finally {
            isSaving.value = false
        }
    }

    /**
     * Delete coupon
     */
    async function deleteCoupon(id: number) {
        const result = await swal.confirm('Bạn chắc chắn muốn xóa mã giảm giá này?')
        if (!result) return

        try {
            await couponService.delete(id)
            await swal.success('Xóa mã giảm giá thành công!')
            await fetchCoupons()
            await fetchStats()
        } catch (error) {
            handleError(error, 'Không thể xóa mã giảm giá')
        }
    }

    /**
     * Open edit modal
     */
    function editCoupon(coupon: Coupon) {
        editingCoupon.value = coupon
        form.value = {
            code: coupon.code,
            name: coupon.name,
            description: coupon.description || '',
            type: coupon.type,
            value: coupon.value,
            min_purchase_amount: coupon.min_purchase_amount,
            max_discount_amount: coupon.max_discount_amount,
            usage_limit_total: coupon.usage_limit_total,
            usage_limit_per_user: coupon.usage_limit_per_user,
            usage_limit_per_day: coupon.usage_limit_per_day,
            auto_apply: coupon.auto_apply,
            stackable: coupon.stackable,
            first_order_only: coupon.first_order_only,
            valid_from: coupon.valid_from?.split('T')[0] || '',
            valid_to: coupon.valid_to?.split('T')[0] || ''
        }
        showCreateModal.value = true
    }

    /**
     * Open create modal
     */
    function openCreateModal() {
        resetForm()
        editingCoupon.value = null
        showCreateModal.value = true
    }

    /**
     * Close modal and reset form
     */
    function closeModal() {
        showCreateModal.value = false
        editingCoupon.value = null
        resetForm()
    }

    /**
     * Reset form to initial state
     */
    function resetForm() {
        form.value = {
            code: '',
            name: '',
            description: '',
            type: 'percentage',
            value: 0,
            min_purchase_amount: null,
            max_discount_amount: null,
            usage_limit_total: null,
            usage_limit_per_user: null,
            usage_limit_per_day: null,
            auto_apply: false,
            stackable: false,
            first_order_only: false,
            valid_from: '',
            valid_to: ''
        }
    }

    /**
     * Generate coupon codes
     */
    async function generateCodes(couponId: number) {
        const { value: quantity } = await swal.prompt('Số lượng mã cần tạo:', '10')
        if (!quantity) return

        const qty = parseInt(quantity)
        if (isNaN(qty) || qty <= 0) {
            await swal.warning('Vui lòng nhập số lượng hợp lệ')
            return
        }

        try {
            await couponService.generateCodes(couponId, qty)
            await swal.success(`Đã tạo ${qty} mã thành công!`)
        } catch (error) {
            handleError(error, 'Không thể tạo mã')
        }
    }

    /**
     * Change page
     */
    function changePage(page: number) {
        currentPage.value = page
        fetchCoupons(page)
    }

    /**
     * Apply filters and fetch
     */
    function applyFilters() {
        currentPage.value = 1
        fetchCoupons(1)
    }

    /**
     * Reset filters
     */
    function resetFilters() {
        filters.value = {
            search: '',
            type: '',
            status: ''
        }
        currentPage.value = 1
        fetchCoupons(1)
    }

    // Type helpers
    const typeLabels: Record<CouponType, string> = {
        percentage: 'Phần trăm',
        fixed: 'Cố định',
        bxgy: 'Buy X Get Y',
        free_shipping: 'Miễn phí'
    }

    const typeBadges: Record<CouponType, string> = {
        percentage: 'bg-blue-100 text-blue-800',
        fixed: 'bg-green-100 text-green-800',
        bxgy: 'bg-purple-100 text-purple-800',
        free_shipping: 'bg-orange-100 text-orange-800'
    }

    function getTypeLabel(type: CouponType) {
        return typeLabels[type] || type
    }

    function getTypeBadge(type: CouponType) {
        return typeBadges[type] || 'bg-gray-100 text-gray-800'
    }

    function formatCurrency(value: number) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(value)
    }

    return {
        // State
        coupons,
        stats,
        selectedCoupon,
        isLoading,
        isSaving,
        hasCoupons,

        // Pagination
        currentPage,
        lastPage,
        total,
        perPage,

        // Filters
        filters,

        // Form
        form,
        editingCoupon,

        // Modals
        showCreateModal,

        // Methods
        fetchCoupons,
        fetchStats,
        saveCoupon,
        deleteCoupon,
        editCoupon,
        openCreateModal,
        closeModal,
        resetForm,
        generateCodes,
        changePage,
        applyFilters,
        resetFilters,

        // Helpers
        getTypeLabel,
        getTypeBadge,
        formatCurrency
    }
}
