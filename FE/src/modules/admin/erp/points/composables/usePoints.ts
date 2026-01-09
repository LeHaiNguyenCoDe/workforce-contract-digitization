/**
 * usePoints Composable
 */
import { ref } from 'vue'
import { useSwal } from '@/utils'
import pointsService from '../services/pointsService'
import type { PointTransaction, CustomerPoints } from '../models/point'

export function usePoints() {
    const swal = useSwal()

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
            // Mock for development
            customerInfo.value = {
                customer_id: 1,
                customer_name: 'Nguyễn Văn An',
                customer_email: 'an@example.com',
                current_points: 5200,
                tier_name: 'Vàng',
                tier_color: '#FFD700',
                total_earned: 12500,
                total_redeemed: 7300
            }
            transactions.value = [
                { id: 1, customer_id: 1, type: 'earn', amount: 500, balance_after: 5200, description: 'Mua hàng #1001', created_at: '2024-12-20' },
                { id: 2, customer_id: 1, type: 'redeem', amount: -1000, balance_after: 4700, description: 'Đổi voucher', created_at: '2024-12-15' },
                { id: 3, customer_id: 1, type: 'earn', amount: 300, balance_after: 5700, description: 'Mua hàng #995', created_at: '2024-12-10' }
            ]
        } catch (error) {
            await swal.error('Không tìm thấy khách hàng!')
        } finally {
            isLoading.value = false
        }
    }

    function openRedeemModal() {
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
            await pointsService.redeemPoints(customerInfo.value.customer_id, redeemAmount.value, redeemDescription.value)
            await swal.success('Đổi điểm thành công!')
            showRedeemModal.value = false
            await searchCustomer()
        } catch (error) {
            await swal.error('Không thể đổi điểm!')
        } finally {
            isSaving.value = false
        }
    }

    return {
        customerInfo, transactions, isLoading, isSaving, searchQuery, currentPage, totalPages,
        showRedeemModal, redeemAmount, redeemDescription,
        searchCustomer, openRedeemModal, handleRedeem
    }
}
