/**
 * useAuditLogs Composable
 */
import { ref, onMounted } from 'vue'
import type { AuditLog } from '../models/auditLog'

const getMockLogs = (): AuditLog[] => [
    { id: 1, user_id: 1, user_name: 'Admin', action: 'update', entity_type: 'Product', entity_id: 123, old_values: { price: 100000 }, new_values: { price: 120000 }, ip_address: '192.168.1.1', created_at: '2024-12-26T10:00:00Z' },
    { id: 2, user_id: 2, user_name: 'Manager', action: 'create', entity_type: 'Order', entity_id: 456, new_values: { total: 500000 }, ip_address: '192.168.1.2', created_at: '2024-12-26T09:30:00Z' },
    { id: 3, user_id: 1, user_name: 'Admin', action: 'delete', entity_type: 'Promotion', entity_id: 789, old_values: { name: 'Flash Sale' }, ip_address: '192.168.1.1', created_at: '2024-12-26T09:00:00Z' },
    { id: 4, user_id: 3, user_name: 'Staff', action: 'login', entity_type: 'Session', entity_id: 0, ip_address: '192.168.1.3', user_agent: 'Chrome/120', created_at: '2024-12-26T08:30:00Z' }
]

export function useAuditLogs() {
    const logs = ref<AuditLog[]>([])
    const isLoading = ref(false)
    const currentPage = ref(1)
    const totalPages = ref(1)
    const actionFilter = ref('')
    const entityFilter = ref('')
    const expandedId = ref<number | null>(null)

    async function fetchLogs() {
        isLoading.value = true
        try {
            await new Promise(r => setTimeout(r, 500))
            logs.value = getMockLogs()
        } finally {
            isLoading.value = false
        }
    }

    function toggleExpand(id: number) {
        expandedId.value = expandedId.value === id ? null : id
    }

    function setActionFilter(action: string) {
        actionFilter.value = action
        fetchLogs()
    }

    function changePage(page: number) {
        currentPage.value = page
        fetchLogs()
    }

    onMounted(fetchLogs)

    return { logs, isLoading, currentPage, totalPages, actionFilter, entityFilter, expandedId, fetchLogs, toggleExpand, setActionFilter, changePage }
}
