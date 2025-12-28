/**
 * useAutomations Composable
 */
import { ref, onMounted } from 'vue'
import { useSwal } from '@/shared/utils'
import type { Automation } from '../models/automation'

const getMockAutomations = (): Automation[] => [
    { id: 1, name: 'Chào mừng khách mới', trigger: 'customer_signup', action: 'send_email', is_active: true, created_at: '2024-01-01' },
    { id: 2, name: 'Nhắc nhở sinh nhật', trigger: 'birthday', action: 'add_points', is_active: true, created_at: '2024-01-15' },
    { id: 3, name: 'Thông báo đơn giao', trigger: 'order_shipped', action: 'send_sms', is_active: false, created_at: '2024-02-01' }
]

export function useAutomations() {
    const swal = useSwal()

    const automations = ref<Automation[]>([])
    const isLoading = ref(false)
    const showModal = ref(false)
    const editingItem = ref<Automation | null>(null)

    const form = ref({ name: '', trigger: 'order_placed' as any, action: 'send_email' as any, is_active: true })

    async function fetchAutomations() {
        isLoading.value = true
        try {
            await new Promise(r => setTimeout(r, 500))
            automations.value = getMockAutomations()
        } finally {
            isLoading.value = false
        }
    }

    function openCreate() {
        editingItem.value = null
        form.value = { name: '', trigger: 'order_placed', action: 'send_email', is_active: true }
        showModal.value = true
    }

    function openEdit(item: Automation) {
        editingItem.value = item
        form.value = { name: item.name, trigger: item.trigger, action: item.action, is_active: item.is_active }
        showModal.value = true
    }

    async function toggleActive(item: Automation) {
        item.is_active = !item.is_active
        await swal.success(item.is_active ? 'Đã bật!' : 'Đã tắt!')
    }

    async function saveAutomation() {
        if (!form.value.name) { await swal.warning('Nhập tên automation!'); return }
        await swal.success(editingItem.value ? 'Đã cập nhật!' : 'Đã tạo!')
        showModal.value = false
        await fetchAutomations()
    }

    async function deleteAutomation(item: Automation) {
        const confirmed = await swal.confirmDelete(`Xóa "${item.name}"?`)
        if (!confirmed) return
        automations.value = automations.value.filter(a => a.id !== item.id)
        await swal.success('Đã xóa!')
    }

    onMounted(fetchAutomations)

    return { automations, isLoading, showModal, editingItem, form, fetchAutomations, openCreate, openEdit, toggleActive, saveAutomation, deleteAutomation }
}
