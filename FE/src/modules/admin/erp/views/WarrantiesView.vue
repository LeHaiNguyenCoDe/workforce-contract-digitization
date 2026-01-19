<script setup lang="ts">
import { ref, onMounted } from 'vue'
import httpClient from '@/plugins/api/httpClient'

interface Warranty {
    id: number
    warranty_code: string
    start_date: string
    end_date: string
    status: 'active' | 'claimed' | 'expired' | 'void'
    product?: { id: number; name: string; sku: string }
    customer?: { id: number; name: string; email: string }
}

const warranties = ref<Warranty[]>([])
const isLoading = ref(true)
const lookupCode = ref('')
const lookupResult = ref<Warranty | null>(null)

const fetchWarranties = async () => {
    isLoading.value = true
    try {
        const res = await httpClient.get('admin/warranties')
        warranties.value = res.data.data?.data || []
    } catch (e) { console.error(e) }
    finally { isLoading.value = false }
}

const lookup = async () => {
    if (!lookupCode.value) return
    try {
        const res = await httpClient.get(`admin/warranties/lookup?code=${lookupCode.value}`)
        lookupResult.value = res.data.data
    } catch (e) {
        lookupResult.value = null
        alert('Không tìm thấy bảo hành')
    }
}

const formatDate = (d: string) => new Date(d).toLocaleDateString('vi-VN')

const getStatusColor = (s: string) => ({
    active: 'bg-success/20 text-success',
    claimed: 'bg-warning/20 text-warning',
    expired: 'bg-error/20 text-error',
    void: 'bg-slate-500/20 text-slate-400',
}[s] || 'bg-slate-500/20 text-slate-400')

const getStatusLabel = (s: string) => ({
    active: 'Còn hiệu lực',
    claimed: 'Đang xử lý',
    expired: 'Hết hạn',
    void: 'Vô hiệu',
}[s] || s)

onMounted(fetchWarranties)
</script>

<template>
    <div>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-white">Quản lý bảo hành</h1>
        </div>

        <!-- Lookup -->
        <div class="card mb-6">
            <h3 class="font-medium text-white mb-3">Tra cứu bảo hành</h3>
            <div class="flex gap-2">
                <input v-model="lookupCode" placeholder="Nhập mã bảo hành..." 
                    class="flex-1 px-4 py-2 rounded-lg bg-dark-700 border border-white/10 text-white" 
                    @keyup.enter="lookup">
                <button @click="lookup" class="px-4 py-2 rounded-lg bg-primary text-white">Tra cứu</button>
            </div>
            <div v-if="lookupResult" class="mt-4 p-4 rounded-lg bg-dark-700">
                <div class="grid md:grid-cols-3 gap-4">
                    <div>
                        <div class="text-xs text-slate-500">Mã bảo hành</div>
                        <div class="text-white font-mono">{{ lookupResult.warranty_code }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500">Sản phẩm</div>
                        <div class="text-white">{{ lookupResult.product?.name }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500">Trạng thái</div>
                        <span :class="['px-2 py-1 text-xs rounded-full', getStatusColor(lookupResult.status)]">
                            {{ getStatusLabel(lookupResult.status) }}
                        </span>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500">Ngày bắt đầu</div>
                        <div class="text-white">{{ formatDate(lookupResult.start_date) }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500">Ngày hết hạn</div>
                        <div class="text-white">{{ formatDate(lookupResult.end_date) }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500">Khách hàng</div>
                        <div class="text-white">{{ lookupResult.customer?.name || '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- List -->
        <div class="card overflow-hidden">
            <div v-if="isLoading" class="p-8 text-center text-slate-400">Đang tải...</div>
            <div v-else-if="!warranties.length" class="p-8 text-center text-slate-400">Chưa có phiếu bảo hành</div>
            <table v-else class="w-full">
                <thead class="bg-dark-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Mã</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Sản phẩm</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Khách hàng</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Hiệu lực</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Trạng thái</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <tr v-for="w in warranties" :key="w.id" class="hover:bg-dark-700/50">
                        <td class="px-4 py-3 font-mono text-primary">{{ w.warranty_code }}</td>
                        <td class="px-4 py-3 text-white">{{ w.product?.name }}</td>
                        <td class="px-4 py-3 text-slate-400">{{ w.customer?.name || '-' }}</td>
                        <td class="px-4 py-3 text-slate-400">{{ formatDate(w.start_date) }} - {{ formatDate(w.end_date) }}</td>
                        <td class="px-4 py-3">
                            <span :class="['px-2 py-1 text-xs rounded-full', getStatusColor(w.status)]">{{ getStatusLabel(w.status) }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
