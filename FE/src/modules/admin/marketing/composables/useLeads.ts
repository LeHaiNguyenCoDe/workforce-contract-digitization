/**
 * useLeads Composable - Lead management state and operations
 */
import { ref, computed } from 'vue'
import { useSwal, useErrorHandler } from '@/utils'
import leadService from '../services/leadService'
import type { Lead, LeadStats, LeadFilters, LeadForm } from '../models/lead'

export function useLeads() {
    const swal = useSwal()
    const { handleError } = useErrorHandler()

    // State
    const leads = ref<Lead[]>([])
    const stats = ref<LeadStats>({
        total_leads: 0,
        by_status: {} as any,
        by_temperature: {} as any,
        by_source: {} as any,
        conversion_rate: 0,
        total_value: 0,
        avg_score: 0
    })
    const selectedLead = ref<Lead | null>(null)
    const isLoading = ref(false)
    const isSaving = ref(false)

    // Pagination
    const currentPage = ref(1)
    const lastPage = ref(1)
    const total = ref(0)
    const perPage = ref(20)

    // Filters
    const filters = ref<LeadFilters>({
        search: '',
        status: '',
        temperature: '',
        source: ''
    })

    // Form
    const form = ref<LeadForm>({
        full_name: '',
        email: '',
        phone: '',
        company: '',
        source: '',
        notes: ''
    })

    // Modals
    const showCreateModal = ref(false)
    const showImportModal = ref(false)
    const editingLead = ref<Lead | null>(null)

    // Computed
    const hasLeads = computed(() => leads.value.length > 0)

    /**
     * Fetch leads with current filters
     */
    async function fetchLeads(page?: number) {
        isLoading.value = true
        try {
            const result = await leadService.getAll(
                filters.value,
                page || currentPage.value,
                perPage.value
            )
            leads.value = [...result.data]
            currentPage.value = result.current_page
            lastPage.value = result.last_page
            total.value = result.total
        } catch (error) {
            handleError(error, 'Không thể tải danh sách leads')
            leads.value = []
        } finally {
            isLoading.value = false
        }
    }

    /**
     * Fetch lead statistics
     */
    async function fetchStats() {
        try {
            const result = await leadService.getStats()
            stats.value = { ...result }
        } catch (error) {
            console.error('Error fetching stats:', error)
        }
    }

    /**
     * Create or update lead
     */
    async function saveLead() {
        if (!form.value.full_name?.trim()) {
            await swal.warning('Vui lòng nhập tên đầy đủ')
            return false
        }

        isSaving.value = true
        try {
            if (editingLead.value) {
                await leadService.update(editingLead.value.id, form.value)
                await swal.success('Cập nhật lead thành công!')
            } else {
                await leadService.create(form.value)
                await swal.success('Tạo lead mới thành công!')
            }

            closeModal()
            await fetchLeads()
            await fetchStats()
            return true
        } catch (error) {
            handleError(error, 'Không thể lưu lead')
            return false
        } finally {
            isSaving.value = false
        }
    }

    /**
     * Delete lead
     */
    async function deleteLead(id: number) {
        const result = await swal.confirm('Bạn chắc chắn muốn xóa lead này?')
        if (!result.isConfirmed) return

        try {
            await leadService.delete(id)
            await swal.success('Xóa lead thành công!')
            await fetchLeads()
            await fetchStats()
        } catch (error) {
            handleError(error, 'Không thể xóa lead')
        }
    }

    /**
     * View lead details
     */
    async function viewLead(lead: Lead) {
        try {
            const fullLead = await leadService.getById(lead.id)
            selectedLead.value = fullLead
        } catch (error) {
            handleError(error, 'Không thể tải thông tin lead')
        }
    }

    /**
     * Open edit modal
     */
    function editLead(lead: Lead) {
        editingLead.value = lead
        form.value = {
            full_name: lead.full_name,
            email: lead.email || '',
            phone: lead.phone || '',
            company: lead.company || '',
            source: lead.source || '',
            notes: lead.notes || ''
        }
        showCreateModal.value = true
    }

    /**
     * Open create modal
     */
    function openCreateModal() {
        resetForm()
        editingLead.value = null
        showCreateModal.value = true
    }

    /**
     * Close modal and reset form
     */
    function closeModal() {
        showCreateModal.value = false
        showImportModal.value = false
        editingLead.value = null
        resetForm()
    }

    /**
     * Reset form to initial state
     */
    function resetForm() {
        form.value = {
            full_name: '',
            email: '',
            phone: '',
            company: '',
            source: '',
            notes: ''
        }
    }

    /**
     * Change page
     */
    function changePage(page: number) {
        currentPage.value = page
        fetchLeads(page)
    }

    /**
     * Apply filters and fetch
     */
    function applyFilters() {
        currentPage.value = 1
        fetchLeads(1)
    }

    /**
     * Reset filters
     */
    function resetFilters() {
        filters.value = {
            search: '',
            status: '',
            temperature: '',
            source: ''
        }
        currentPage.value = 1
        fetchLeads(1)
    }

    /**
     * Import leads from CSV
     */
    async function importLeads(file: File) {
        isSaving.value = true
        try {
            const result = await leadService.importFromCsv(file)
            await swal.success(`Import thành công ${result.imported || 0} leads!`)
            showImportModal.value = false
            await fetchLeads()
            await fetchStats()
        } catch (error) {
            handleError(error, 'Không thể import leads')
        } finally {
            isSaving.value = false
        }
    }

    // Status/Temperature helpers
    const statusLabels: Record<string, string> = {
        new: 'Mới',
        contacted: 'Đã liên hệ',
        qualified: 'Hợp lệ',
        proposal: 'Báo giá',
        negotiation: 'Đàm phán',
        converted: 'Đã chuyển',
        lost: 'Mất',
        disqualified: 'Không hợp lệ'
    }

    const statusBadges: Record<string, string> = {
        new: 'bg-blue-100 text-blue-800',
        contacted: 'bg-purple-100 text-purple-800',
        qualified: 'bg-green-100 text-green-800',
        proposal: 'bg-yellow-100 text-yellow-800',
        negotiation: 'bg-orange-100 text-orange-800',
        converted: 'bg-emerald-100 text-emerald-800',
        lost: 'bg-red-100 text-red-800',
        disqualified: 'bg-gray-100 text-gray-800'
    }

    const temperatureLabels: Record<string, string> = {
        hot: '🔥 Nóng',
        warm: '🌡️ Ấm',
        cold: '❄️ Lạnh'
    }

    const temperatureBadges: Record<string, string> = {
        hot: 'bg-red-100 text-red-800',
        warm: 'bg-orange-100 text-orange-800',
        cold: 'bg-blue-100 text-blue-800'
    }

    function getStatusLabel(status: string) {
        return statusLabels[status] || status
    }

    function getStatusBadge(status: string) {
        return statusBadges[status] || 'bg-gray-100 text-gray-800'
    }

    function getTemperatureLabel(temp: string) {
        return temperatureLabels[temp] || temp
    }

    function getTemperatureBadge(temp: string) {
        return temperatureBadges[temp] || 'bg-gray-100 text-gray-800'
    }

    return {
        // State
        leads,
        stats,
        selectedLead,
        isLoading,
        isSaving,
        hasLeads,

        // Pagination
        currentPage,
        lastPage,
        total,
        perPage,

        // Filters
        filters,

        // Form
        form,
        editingLead,

        // Modals
        showCreateModal,
        showImportModal,

        // Methods
        fetchLeads,
        fetchStats,
        saveLead,
        deleteLead,
        viewLead,
        editLead,
        openCreateModal,
        closeModal,
        resetForm,
        changePage,
        applyFilters,
        resetFilters,
        importLeads,

        // Helpers
        getStatusLabel,
        getStatusBadge,
        getTemperatureLabel,
        getTemperatureBadge
    }
}
