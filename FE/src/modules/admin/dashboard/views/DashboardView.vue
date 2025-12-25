<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { adminOrderService, adminProductService, adminUserService } from '@/plugins/api'

const { t } = useI18n()

const stats = ref({
    orders: 0,
    products: 0,
    users: 0,
    revenue: 0
})
const isLoading = ref(true)

const fetchStats = async () => {
    try {
        const [ordersRes, productsRes, usersRes] = await Promise.all([
            adminOrderService.getAll({ limit: 1 }),
            adminProductService.getAll({ limit: 1 }),
            adminUserService.getAll({ limit: 1 })
        ])
        stats.value = {
            orders: ordersRes.meta?.total || 0,
            products: productsRes.meta?.total || 0,
            users: usersRes.meta?.total || 0,
            revenue: 125000000
        }
    } catch (error) {
        console.error('Failed to fetch stats:', error)
    } finally {
        isLoading.value = false
    }
}

const formatNumber = (num: number) => {
    return new Intl.NumberFormat('vi-VN').format(num)
}

const formatCurrency = (num: number) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(num)
}

onMounted(fetchStats)
</script>

<template>
    <div>
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Orders -->
            <div class="card flex items-center gap-4">
                <div class="w-14 h-14 flex items-center justify-center rounded-xl bg-primary/20 text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" />
                        <path d="M3 6h18" />
                    </svg>
                </div>
                <div>
                    <div class="text-2xl font-bold text-white">{{ isLoading ? '...' : formatNumber(stats.orders) }}
                    </div>
                    <div class="text-sm text-slate-400">{{ t('admin.orders') }}</div>
                </div>
            </div>

            <!-- Products -->
            <div class="card flex items-center gap-4">
                <div class="w-14 h-14 flex items-center justify-center rounded-xl bg-success/20 text-success">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="m7.5 4.27 9 5.15" />
                        <path
                            d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" />
                    </svg>
                </div>
                <div>
                    <div class="text-2xl font-bold text-white">{{ isLoading ? '...' : formatNumber(stats.products) }}
                    </div>
                    <div class="text-sm text-slate-400">{{ t('admin.products') }}</div>
                </div>
            </div>

            <!-- Users -->
            <div class="card flex items-center gap-4">
                <div class="w-14 h-14 flex items-center justify-center rounded-xl bg-secondary/20 text-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                    </svg>
                </div>
                <div>
                    <div class="text-2xl font-bold text-white">{{ isLoading ? '...' : formatNumber(stats.users) }}</div>
                    <div class="text-sm text-slate-400">{{ t('admin.users') }}</div>
                </div>
            </div>

            <!-- Revenue -->
            <div class="card flex items-center gap-4">
                <div class="w-14 h-14 flex items-center justify-center rounded-xl bg-warning/20 text-warning">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <line x1="12" x2="12" y1="2" y2="22" />
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                    </svg>
                </div>
                <div>
                    <div class="text-2xl font-bold text-white">{{ isLoading ? '...' : formatCurrency(stats.revenue) }}
                    </div>
                    <div class="text-sm text-slate-400">{{ t('admin.revenue') }}</div>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="grid md:grid-cols-2 gap-6">
            <!-- Recent Orders -->
            <div class="card">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-white">{{ t('admin.recentOrders') }}</h3>
                    <a href="/admin/orders" class="text-sm text-primary hover:text-primary-light">{{ t('common.viewAll')
                        }} →</a>
                </div>
                <div class="space-y-3">
                    <div v-for="i in 5" :key="i"
                        class="flex items-center justify-between py-3 border-b border-white/5 last:border-0">
                        <div>
                            <div class="font-medium text-white">#ORD-{{ 1000 + i }}</div>
                            <div class="text-sm text-slate-500">Khách hàng {{ i }}</div>
                        </div>
                        <span class="px-2 py-1 text-xs font-medium rounded-full"
                            :class="i % 3 === 0 ? 'bg-success/20 text-success' : i % 3 === 1 ? 'bg-warning/20 text-warning' : 'bg-info/20 text-info'">
                            {{ i % 3 === 0 ? 'Đã giao' : i % 3 === 1 ? 'Đang xử lý' : 'Đang giao' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Top Products -->
            <div class="card">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-white">{{ t('admin.topProducts') }}</h3>
                    <a href="/admin/products" class="text-sm text-primary hover:text-primary-light">{{
                        t('common.viewAll') }} →</a>
                </div>
                <div class="space-y-3">
                    <div v-for="i in 5" :key="i"
                        class="flex items-center gap-4 py-3 border-b border-white/5 last:border-0">
                        <div class="w-12 h-12 bg-dark-700 rounded-lg"></div>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-white truncate">Sản phẩm {{ i }}</div>
                            <div class="text-sm text-slate-500">{{ 50 + i * 12 }} đã bán</div>
                        </div>
                        <div class="font-medium gradient-text">{{ formatCurrency(199000 * i) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
