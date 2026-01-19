<script setup lang="ts">
import { ref, onMounted } from 'vue'
import httpClient from '@/plugins/api/httpClient'

interface ApiLog {
    id: number
    method: string
    endpoint: string
    status_code: number
    duration_ms: number
    created_at: string
    user?: { id: number; name: string }
}

interface Stats {
    total_requests: number
    error_count: number
    avg_duration: number
    slow_requests: number
}

const logs = ref<ApiLog[]>([])
const stats = ref<Stats | null>(null)
const isLoading = ref(true)
const filter = ref({ method: '', status: '' })

const fetchData = async () => {
    isLoading.value = true
    try {
        const [logsRes, statsRes] = await Promise.all([
            httpClient.get('admin/api-logs'),
            httpClient.get('admin/api-logs/stats')
        ])
        logs.value = logsRes.data.data?.data || []
        stats.value = statsRes.data.data || null
    } catch (e) { console.error(e) }
    finally { isLoading.value = false }
}

const getMethodColor = (m: string) => ({
    GET: 'bg-success/20 text-success',
    POST: 'bg-info/20 text-info',
    PUT: 'bg-warning/20 text-warning',
    PATCH: 'bg-warning/20 text-warning',
    DELETE: 'bg-error/20 text-error',
}[m] || 'bg-slate-500/20 text-slate-400')

const getStatusColor = (s: number) => {
    if (s < 300) return 'text-success'
    if (s < 400) return 'text-info'
    if (s < 500) return 'text-warning'
    return 'text-error'
}

onMounted(fetchData)
</script>

<template>
    <div>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-white">API Logs & Monitoring</h1>
        </div>

        <!-- Stats -->
        <div v-if="stats" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="card">
                <div class="text-sm text-slate-400">Tổng requests (24h)</div>
                <div class="text-2xl font-bold text-white">{{ stats.total_requests.toLocaleString() }}</div>
            </div>
            <div class="card">
                <div class="text-sm text-slate-400">Lỗi</div>
                <div class="text-2xl font-bold text-error">{{ stats.error_count }}</div>
            </div>
            <div class="card">
                <div class="text-sm text-slate-400">Thời gian TB</div>
                <div class="text-2xl font-bold text-info">{{ stats.avg_duration }}ms</div>
            </div>
            <div class="card">
                <div class="text-sm text-slate-400">Requests chậm</div>
                <div class="text-2xl font-bold text-warning">{{ stats.slow_requests }}</div>
            </div>
        </div>

        <!-- Logs Table -->
        <div class="card overflow-hidden">
            <div v-if="isLoading" class="p-8 text-center text-slate-400">Đang tải...</div>
            <div v-else-if="!logs.length" class="p-8 text-center text-slate-400">Chưa có logs</div>
            <table v-else class="w-full">
                <thead class="bg-dark-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Method</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Endpoint</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Duration</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">User</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <tr v-for="log in logs" :key="log.id" class="hover:bg-dark-700/50">
                        <td class="px-4 py-3">
                            <span :class="['px-2 py-1 text-xs font-mono rounded', getMethodColor(log.method)]">{{ log.method }}</span>
                        </td>
                        <td class="px-4 py-3 font-mono text-xs text-slate-300 max-w-xs truncate">{{ log.endpoint }}</td>
                        <td class="px-4 py-3">
                            <span :class="['font-medium', getStatusColor(log.status_code)]">{{ log.status_code }}</span>
                        </td>
                        <td class="px-4 py-3 text-slate-400">
                            <span :class="log.duration_ms > 1000 ? 'text-warning' : ''">{{ log.duration_ms }}ms</span>
                        </td>
                        <td class="px-4 py-3 text-slate-400">{{ log.user?.name || '-' }}</td>
                        <td class="px-4 py-3 text-xs text-slate-500">{{ new Date(log.created_at).toLocaleString('vi-VN') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
