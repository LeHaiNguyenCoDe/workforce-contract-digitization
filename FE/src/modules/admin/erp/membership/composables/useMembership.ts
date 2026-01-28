/**
 * useMembership Composable
 */
import { ref, onMounted } from 'vue'
import { useSwal, useErrorHandler } from '@/utils'
import tierService from '../services/tierService'
import type { MembershipTier, CreateTierPayload } from '../models/tier'

export function useMembership() {
    const swal = useSwal()
    const { handleError } = useErrorHandler()

    const tiers = ref<MembershipTier[]>([])
    const isLoading = ref(false)
    const isSaving = ref(false)

    const showModal = ref(false)
    const editingTier = ref<MembershipTier | null>(null)

    const form = ref<CreateTierPayload>({
        name: '', code: '', min_points: 0, max_points: null, discount_percent: 0, point_multiplier: 1, color: '#6366f1', benefits: []
    })
    const newBenefit = ref('')

    async function fetchTiers() {
        isLoading.value = true
        try {
            const result = await tierService.getAll()
            tiers.value = result
        } catch (error) {
            handleError(error, 'Không thể tải danh sách hạng thành viên')
            tiers.value = []
        } finally {
            isLoading.value = false
        }
    }

    function openCreate() {
        editingTier.value = null
        form.value = { name: '', code: '', min_points: 0, max_points: null, discount_percent: 0, point_multiplier: 1, color: '#6366f1', benefits: [] }
        showModal.value = true
    }

    function openEdit(tier: MembershipTier) {
        editingTier.value = tier
        form.value = { 
            name: tier.name, 
            code: tier.code, 
            min_points: tier.min_points, 
            max_points: tier.max_points,
            discount_percent: tier.discount_percent, 
            point_multiplier: tier.point_multiplier,
            color: tier.color, 
            benefits: [...tier.benefits] 
        }
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
        if (!form.value.code) {
            await swal.warning('Vui lòng nhập mã hạng!')
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
            handleError(error, 'Không thể lưu hạng thành viên')
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
            handleError(error, 'Không thể xóa hạng thành viên')
        }
    }

    onMounted(fetchTiers)

    return {
        tiers, isLoading, isSaving, showModal, editingTier, form, newBenefit,
        fetchTiers, openCreate, openEdit, addBenefit, removeBenefit, saveTier, deleteTier
    }
}
