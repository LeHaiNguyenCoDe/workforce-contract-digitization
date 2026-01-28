/**
 * useAutomations Composable
 */
import { ref, onMounted } from 'vue'
import { useSwal, useErrorHandler } from '@/utils'
import type { Automation } from '../models/automation'
import automationService from '../services/automationService'

export function useAutomations() {
    const swal = useSwal()
    const { handleError } = useErrorHandler()

    const automations = ref<Automation[]>([])
    const isLoading = ref(false)
    const showModal = ref(false)
    const editingItem = ref<Automation | null>(null)

    const form = ref({ name: '', trigger: 'order_placed' as any, action: 'email' as any, is_active: true })

    async function fetchAutomations() {
        isLoading.value = true
        try {
            const data = await automationService.getAll()
            automations.value = data
        } catch (error) {
            handleError(error, 'Không thể tải danh sách tự động hóa')
            automations.value = []
        } finally {
            isLoading.value = false
        }
    }

    function openCreate() {
        editingItem.value = null
        form.value = { name: '', trigger: 'order_placed', action: 'email', is_active: true }
        showModal.value = true
    }

    function openEdit(item: Automation) {
        editingItem.value = item
        form.value = { name: item.name, trigger: item.trigger, action: item.action, is_active: item.is_active }
        showModal.value = true
    }

    async function toggleActive(item: Automation) {
        try {
            await automationService.toggleActive(item.id)
            item.is_active = !item.is_active
            await swal.success(item.is_active ? 'Đã bật!' : 'Đã tắt!')
        } catch (error) {
            handleError(error, 'Không thể thay đổi trạng thái')
        }
    }

    async function saveAutomation() {
        if (!form.value.name) { await swal.warning('Nhập tên automation!'); return }
        
        try {
            if (editingItem.value) {
                await automationService.update(editingItem.value.id, form.value)
                await swal.success('Đã cập nhật!')
            } else {
                await automationService.create(form.value)
                await swal.success('Đã tạo thành công!')
            }
            showModal.value = false
            await fetchAutomations()
        } catch (error) {
            handleError(error, 'Không thể lưu tự động hóa')
        }
    }

    async function deleteAutomation(item: Automation) {
        const confirmed = await swal.confirmDelete(`Xóa "${item.name}"?`)
        if (!confirmed) return
        
        try {
            await automationService.delete(item.id)
            await swal.success('Đã xóa!')
            await fetchAutomations()
        } catch (error) {
            handleError(error, 'Không thể xóa tự động hóa')
        }
    }

    onMounted(fetchAutomations)

    return { automations, isLoading, showModal, editingItem, form, fetchAutomations, openCreate, openEdit, toggleActive, saveAutomation, deleteAutomation }
}
