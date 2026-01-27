<script setup lang="ts">
import { ref, onMounted } from 'vue'
import httpClient from '@/plugins/api/httpClient'

interface InventoryReport {
    stocks: Array<{ product_id: number; product_name: string; sku: string; warehouse_name: string; quantity: number }>
    low_stock_count: number
    low_stock: Array<{ id: number; name: string; quantity: number; min_quantity: number }>
    expiring_soon: Array<{ name: string; batch_code: string; expiry_date: string; remaining_quantity: number }>
}

const report = ref<InventoryReport | null>(null)
const isLoading = ref(true)
const searchQuery = ref('')

async function fetchReport() {
    try {
        isLoading.value = true
        const response = await httpClient.get('/admin/reports/inventory')
        report.value = (response.data as any).data
    } catch (error) {
        console.error('Failed to fetch report:', error)
    } finally {
        isLoading.value = false
    }
}

function formatDate(date: string): string {
    return new Date(date).toLocaleDateString('vi-VN')
}

function getDaysUntilExpiry(date: string): number {
    return Math.ceil((new Date(date).getTime() - Date.now()) / (1000 * 60 * 60 * 24))
}

onMounted(() => fetchReport())
</script>

<template>
    <div class="h-full flex flex-col p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-white">Báo cáo Tồn kho</h1>
                <p class="text-slate-400 mt-1">Tồn kho thực tế, cảnh báo hết hàng và sắp hết hạn</p>
            </div>
        </div>

        <div class="flex-1 overflow-auto">
            <div v-if="isLoading" class="flex items-center justify-center h-64">
                <div class="inline-block w-8 h-8 border-4 border-primary/20 border-t-primary rounded-full animate-spin">
                </div>
            </div>

            <div v-else-if="report" class="space-y-6">
                <!-- Alerts -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Low Stock Alert -->
                    <div class="bg-dark-800 rounded-xl border border-warning/30 p-4">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-warning text-xl">⚠️</span>
                            <h3 class="text-lg font-semibold text-warning">Sắp hết hàng ({{ report.low_stock_count }})
                            </h3>
                        </div>
                        <div v-if="report.low_stock.length === 0" class="text-slate-400 text-sm">
                            Không có sản phẩm nào sắp hết hàng
                        </div>
                        <div v-else class="space-y-2 max-h-40 overflow-auto">
                            <div v-for="item in report.low_stock" :key="item.id"
                                class="flex justify-between items-center py-1 text-sm">
                                <span class="text-white">{{ item.name }}</span>
                                <span class="text-warning">{{ item.quantity }} / {{ item.min_quantity }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Expiring Soon Alert -->
                    <div class="bg-dark-800 rounded-xl border border-error/30 p-4">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-error text-xl">🕐</span>
                            <h3 class="text-lg font-semibold text-error">Sắp hết hạn ({{ report.expiring_soon.length }})
                            </h3>
                        </div>
                        <div v-if="report.expiring_soon.length === 0" class="text-slate-400 text-sm">
                            Không có lô hàng nào sắp hết hạn trong 30 ngày tới
                        </div>
                        <div v-else class="space-y-2 max-h-40 overflow-auto">
                            <div v-for="item in report.expiring_soon" :key="item.batch_code"
                                class="flex justify-between items-center py-1 text-sm">
                                <div>
                                    <span class="text-white">{{ item.name }}</span>
                                    <span class="text-slate-500 ml-2">{{ item.batch_code }}</span>
                                </div>
                                <span class="text-error">{{ getDaysUntilExpiry(item.expiry_date) }} ngày</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stock Table -->
                <div class="bg-dark-800 rounded-xl border border-white/10 overflow-hidden">
                    <div class="px-4 py-3 border-b border-white/10 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-white">Tồn kho chi tiết</h3>
                        <input v-model="searchQuery" type="text" placeholder="Tìm kiếm..." class="form-input w-64" />
                    </div>
                    <div class="max-h-96 overflow-auto">
                        <table class="w-full">
                            <thead class="bg-dark-700 sticky top-0">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs text-slate-400">SKU</th>
                                    <th class="px-4 py-2 text-left text-xs text-slate-400">Sản phẩm</th>
                                    <th class="px-4 py-2 text-left text-xs text-slate-400">Kho</th>
                                    <th class="px-4 py-2 text-right text-xs text-slate-400">Tồn kho</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <tr v-for="stock in report.stocks.filter(s =>
                                    !searchQuery || s.product_name.toLowerCase().includes(searchQuery.toLowerCase()) || s.sku.toLowerCase().includes(searchQuery.toLowerCase())
                                )" :key="`${stock.product_id}-${stock.warehouse_name}`" class="hover:bg-white/5">
                                    <td class="px-4 py-2 font-mono text-slate-400 text-sm">{{ stock.sku }}</td>
                                    <td class="px-4 py-2 text-white">{{ stock.product_name }}</td>
                                    <td class="px-4 py-2 text-slate-400">{{ stock.warehouse_name }}</td>
                                    <td class="px-4 py-2 text-right font-medium"
                                        :class="stock.quantity > 0 ? 'text-success' : 'text-error'">
                                        {{ stock.quantity }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
