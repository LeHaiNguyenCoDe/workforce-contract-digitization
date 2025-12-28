/**
 * useMembership Composable
 */
import { ref, onMounted } from 'vue'
import { useSwal } from '@/shared/utils'
import tierService from '../services/tierService'
import type { MembershipTier, CreateTierPayload } from '../models/tier'

const getMockTiers = (): MembershipTier[] => [
    { id: 1, name: 'Đồng', min_points: 0, discount_percent: 0, color: '#CD7F32', benefits: ['Tích điểm 1%', 'Nhận thông báo'], member_count: 1250, created_at: '2024-01-01', updated_at: '2024-01-01' },
    { id: 2, name: 'Bạc', min_points: 1000, discount_percent: 5, color: '#C0C0C0', benefits: ['Tích điểm 2%', 'Giảm 5%', 'Ưu tiên hỗ trợ'], member_count: 450, created_at: '2024-01-01', updated_at: '2024-01-01' },
    { id: 3, name: 'Vàng', min_points: 5000, discount_percent: 10, color: '#FFD700', benefits: ['Tích điểm 3%', 'Giảm 10%', 'Free ship', 'Quà sinh nhật'], member_count: 120, created_at: '2024-01-01', updated_at: '2024-01-01' },
    { id: 4, name: 'Kim cương', min_points: 20000, discount_percent: 15, color: '#B9F2FF', benefits: ['Tích điểm 5%', 'Giảm 15%', 'Free ship', 'Quà VIP', 'Hotline riêng'], member_count: 28, created_at: '2024-01-01', updated_at: '2024-01-01' }
]

export function useMembership() {
    const swal = useSwal()

    const tiers = ref<MembershipTier[]>([])
    const isLoading = ref(false)
    const isSaving = ref(false)

    const showModal = ref(false)
    const editingTier = ref<MembershipTier | null>(null)

    const form = ref<CreateTierPayload>({
        name: '', min_points: 0, discount_percent: 0, color: '#6366f1', benefits: []
    })
    const newBenefit = ref('')

    async function fetchTiers() {
        isLoading.value = true
        try {
            const result = await tierService.getAll()
            // Use mock data if API returns empty array
            tiers.value = result.length > 0 ? result : getMockTiers()
        } catch (error) {
            console.error('Failed to fetch tiers:', error)
            tiers.value = getMockTiers()
        } finally {
            isLoading.value = false
        }
    }

    function openCreate() {
        editingTier.value = null
        form.value = { name: '', min_points: 0, discount_percent: 0, color: '#6366f1', benefits: [] }
        showModal.value = true
    }

    function openEdit(tier: MembershipTier) {
        editingTier.value = tier
        form.value = { name: tier.name, min_points: tier.min_points, discount_percent: tier.discount_percent, color: tier.color, benefits: [...tier.benefits] }
        showModal.value = true
    }

    function addBenefit() {
        if (newBenefit.value.trim()) {
            form.value.benefits.push(newBenefit.value.trim())
            newBenefit.value = ''
        }
    }

    function removeBenefit(index: number) {
        form.value.benefits.splice(index, 1)
    }

    async function saveTier() {
        if (!form.value.name) {
            await swal.warning('Vui lòng nhập tên hạng!')
            return
        }

        isSaving.value = true
        try {
            if (editingTier.value) {
                await tierService.update(editingTier.value.id, form.value)
                await swal.success('Cập nhật thành công!')
            } else {
                await tierService.create(form.value)
                await swal.success('Tạo hạng thành công!')
            }
            showModal.value = false
            await fetchTiers()
        } catch (error) {
            await swal.error('Có lỗi xảy ra!')
        } finally {
            isSaving.value = false
        }
    }

    async function deleteTier(tier: MembershipTier) {
        const confirmed = await swal.confirmDelete(`Xóa hạng ${tier.name}?`)
        if (!confirmed) return

        try {
            await tierService.delete(tier.id)
            await swal.success('Đã xóa!')
            await fetchTiers()
        } catch (error) {
            await swal.error('Không thể xóa!')
        }
    }

    onMounted(fetchTiers)

    return {
        tiers, isLoading, isSaving, showModal, editingTier, form, newBenefit,
        fetchTiers, openCreate, openEdit, addBenefit, removeBenefit, saveTier, deleteTier
    }
}
