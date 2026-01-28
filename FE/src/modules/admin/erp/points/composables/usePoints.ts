/**
 * usePoints Composable
 */
import { ref } from 'vue'
import { useSwal, useErrorHandler } from '@/utils'
import pointsService from '../services/pointsService'
import type { PointTransaction, CustomerPoints } from '../models/point'

export function usePoints() {
    const swal = useSwal()
    const { handleError } = useErrorHandler()

    const customerInfo = ref<CustomerPoints | null>(null)
    const transactions = ref<PointTransaction[]>([])
    const isLoading = ref(false)
    const isSaving = ref(false)
    const searchQuery = ref('')
    const currentPage = ref(1)
    const totalPages = ref(1)

    const showRedeemModal = ref(false)
    const redeemAmount = ref(0)
    const redeemDescription = ref('')

    async function searchCustomer() {
        if (!searchQuery.value) return

        isLoading.value = true
        try {
            // Assume searchQuery is customer ID for simplicity in this ERP part
            // In a better UX, this would be a search-and-select
            const customerId = parseInt(searchQuery.value)
            if (isNaN(customerId)) {
                await swal.warning('Vui lòng nhập ID khách hàng (số)!')
                return
            }

            const info = await pointsService.getCustomerPoints(customerId)
            // Force Vue reactivity by creating new object
            customerInfo.value = { ...info }

            const transResponse = await pointsService.getTransactions(customerId, {
                page: currentPage.value,
                per_page: 10
            })
            // Force Vue reactivity by creating new array
            transactions.value = [...transResponse.data]
            totalPages.value = transResponse.last_page
        } catch (error) {
            handleError(error, 'Không tìm thấy khách hàng hoặc lỗi hệ thống')
            customerInfo.value = null
            transactions.value = []
        } finally {
            isLoading.value = false
        }
    }

    function openRedeemModal() {
        if (!customerInfo.value) return
        redeemAmount.value = 0
        redeemDescription.value = ''
        showRedeemModal.value = true
    }

    async function handleRedeem() {
        if (!customerInfo.value || redeemAmount.value <= 0) {
            await swal.warning('Vui lòng nhập số điểm cần đổi!')
            return
        }
        if (redeemAmount.value > customerInfo.value.current_points) {
            await swal.warning('Số điểm không đủ!')
            return
        }

        isSaving.value = true
        try {
            console.log('Điểm trước khi đổi:', customerInfo.value.current_points)
            const response = await pointsService.redeemPoints(customerInfo.value.customer_id, redeemAmount.value, redeemDescription.value)
            console.log('Redeem response:', response)

            await swal.success('Đổi điểm thành công!')
            showRedeemModal.value = false

            // Refresh customer info
            await searchCustomer()
            console.log('Điểm sau khi refresh:', customerInfo.value?.current_points)
        } catch (error) {
            handleError(error, 'Không thể đổi điểm')
        } finally {
            isSaving.value = false
        }
    }

    function changePage(page: number) {
        currentPage.value = page
        searchCustomer()
    }

    return {
        customerInfo, transactions, isLoading, isSaving, searchQuery, currentPage, totalPages,
        showRedeemModal, redeemAmount, redeemDescription,
        searchCustomer, openRedeemModal, handleRedeem, changePage
    }
}
