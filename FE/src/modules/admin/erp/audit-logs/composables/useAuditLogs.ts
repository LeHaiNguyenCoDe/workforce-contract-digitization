/**
 * useAuditLogs Composable
 */
import { ref, onMounted } from 'vue'
import type { AuditLog } from '../models/auditLog'
import auditLogService from '../services/auditLogService'
import { useErrorHandler } from '@/utils'

export function useAuditLogs() {
    const logs = ref<AuditLog[]>([])
    const isLoading = ref(false)
    const currentPage = ref(1)
    const totalPages = ref(1)
    const actionFilter = ref('')
    const entityFilter = ref('')
    const expandedId = ref<number | null>(null)
    const { handleError } = useErrorHandler()

    async function fetchLogs() {
        isLoading.value = true
        try {
            const params: Record<string, any> = {
                page: currentPage.value,
                per_page: 20
            }
            if (actionFilter.value) params.action = actionFilter.value
            if (entityFilter.value) params.entity_type = entityFilter.value

            const response = await auditLogService.getAll(params)
            logs.value = response?.items || []
            totalPages.value = response?.meta?.last_page || 1
            currentPage.value = response?.meta?.current_page || 1
        } catch (error) {
            handleError(error, 'Không thể tải nhật ký hệ thống')
            logs.value = []
        } finally {
            isLoading.value = false
        }
    }

    function toggleExpand(id: number) {
        expandedId.value = expandedId.value === id ? null : id
    }

    function setActionFilter(action: string) {
        actionFilter.value = action
        currentPage.value = 1
        fetchLogs()
    }

    function changePage(page: number) {
        currentPage.value = page
        fetchLogs()
    }

    onMounted(fetchLogs)

    return { logs, isLoading, currentPage, totalPages, actionFilter, entityFilter, expandedId, fetchLogs, toggleExpand, setActionFilter, changePage }
}
