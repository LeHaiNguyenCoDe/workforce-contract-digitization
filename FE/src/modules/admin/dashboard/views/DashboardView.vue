<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { reportService } from '@/plugins/api'
import type { DashboardAnalytics, RevenueChartData } from '@/plugins/api'

// State
const analytics = ref<DashboardAnalytics | null>(null)
const isLoading = ref(true)
const dateRange = ref<'7d' | '30d' | '90d'>('30d')
const currentTime = ref(new Date())

const dateRanges = ['7d', '30d', '90d'] as const
const chartPeriods = ['All', '1M', '6M', '1Y'] as const

const handleRangeChange = (range: string) => {
    dateRange.value = range as any
    fetchAnalytics()
}

// Update time every minute
setInterval(() => currentTime.value = new Date(), 60000)

// Computed with safe defaults
const kpis = computed(() => analytics.value?.kpis || {
    revenue: 0, gross_profit: 0, gross_margin: 0, order_count: 0,
    avg_order_value: 0, customer_count: 0, new_customers: 0
})
const alerts = computed(() => analytics.value?.alerts || {
    low_stock: 0, pending_orders: 0, pending_returns: 0, pending_purchase_requests: 0
})
const revenueChart = computed(() => analytics.value?.revenue_chart || [])
const recentOrders = computed(() => analytics.value?.recent_orders || [])
const topProducts = computed(() => analytics.value?.top_products || [])

// Sample data for chart when empty
const sampleChartData = computed(() => {
    if (revenueChart.value.length > 0) return revenueChart.value
    // Generate last 14 days sample
    const data: RevenueChartData[] = []
    for (let i = 13; i >= 0; i--) {
        const date = new Date()
        date.setDate(date.getDate() - i)
        data.push({
            date: date.toISOString().split('T')[0],
            total: Math.floor(Math.random() * 5000000) + 500000,
            count: Math.floor(Math.random() * 10) + 1
        })
    }
    return data
})

const chartMaxValue = computed(() => {
    const data = sampleChartData.value
    if (!data.length) return 100
    return Math.max(...data.map(d => parseFloat(String(d.total)) || 0)) * 1.1 || 100
})

// Fetch data
const fetchAnalytics = async () => {
    isLoading.value = true
    try {
        const days = dateRange.value === '7d' ? 7 : dateRange.value === '30d' ? 30 : 90
        const fromDate = new Date()
        fromDate.setDate(fromDate.getDate() - days)
        analytics.value = await reportService.getDashboardAnalytics({
            from_date: fromDate.toISOString().split('T')[0],
            to_date: new Date().toISOString().split('T')[0]
        })
    } catch (error) {
        console.error('Failed to fetch analytics:', error)
    } finally {
        isLoading.value = false
    }
}

// Formatters
const formatNumber = (n: number) => new Intl.NumberFormat('vi-VN').format(n || 0)
const formatCurrency = (n: number) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(n || 0)
const formatCompact = (n: number) => {
    if (!n) return '0₫'
    if (n >= 1e9) return (n / 1e9).toFixed(1) + ' tỷ'
    if (n >= 1e6) return (n / 1e6).toFixed(1) + 'M'
    if (n >= 1e3) return (n / 1e3).toFixed(1) + 'K'
    return formatNumber(n) + '₫'
}
const formatTime = (d: Date) => d.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })
const formatDate = (s: string) => new Date(s).toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit' })

const getStatusColor = (s: string) => ({
    pending: 'from-amber-500 to-yellow-500',
    confirmed: 'from-blue-500 to-cyan-500',
    processing: 'from-purple-500 to-pink-500',
    shipping: 'from-indigo-500 to-blue-500',
    delivered: 'from-green-500 to-emerald-500',
    completed: 'from-green-500 to-teal-500',
    cancelled: 'from-red-500 to-rose-500',
}[s] || 'from-gray-500 to-slate-500')

const getStatusLabel = (s: string) => ({
    pending: 'Chờ xử lý', confirmed: 'Đã xác nhận', processing: 'Đang xử lý',
    shipping: 'Đang giao', delivered: 'Đã giao', completed: 'Hoàn thành', cancelled: 'Đã hủy'
}[s] || s)

const getChartBarHeight = (t: number) => {
    if (!chartMaxValue.value || !t) return 5
    return Math.max((parseFloat(String(t)) / chartMaxValue.value) * 100, 5)
}

// Chart Options (ApexCharts)
const chartOptions = computed(() => ({
    chart: {
        id: 'revenue-mixed-chart',
        stacked: false,
        toolbar: { show: false },
        fontFamily: 'Inter, sans-serif',
        background: 'transparent'
    },
    stroke: {
        width: [0, 4, 4],
        curve: 'smooth',
        dashArray: [0, 0, 0]
    },
    plotOptions: {
        bar: {
            columnWidth: '35%',
            borderRadius: 6
        }
    },
    fill: {
        opacity: [0.85, 0.25, 1],
        gradient: {
            inverseColors: false,
            shade: 'dark',
            type: "vertical",
            opacityFrom: [0.85, 0.75, 1],
            opacityTo: [0.85, 0.2, 1],
            stops: [0, 100, 100, 100]
        }
    },
    labels: sampleChartData.value.map(d => formatDate(d.date)),
    markers: {
        size: [0, 5, 5],
        strokeWidth: 2,
        strokeColors: '#fff',
        hover: { size: 7 }
    },
    xaxis: {
        type: 'category',
        axisBorder: { show: false },
        axisTicks: { show: false },
        labels: {
            style: {
                colors: '#a0aec0',
                fontSize: '12px'
            }
        }
    },
    yaxis: [
        {
            title: { text: "Orders", style: { color: "#405189" } },
            labels: { style: { colors: "#405189" } }
        },
        {
            opposite: true,
            title: { text: "Earnings (k)", style: { color: "#0ab39c" } },
            labels: { 
                style: { colors: "#0ab39c" },
                formatter: (val: number) => (val / 1000000).toFixed(1) + 'M'
            }
        }
    ],
    grid: {
        borderColor: 'rgba(255, 255, 255, 0.05)',
        padding: { top: 0, bottom: 0, left: 20, right: 20 }
    },
    legend: {
        show: true,
        position: 'top',
        horizontalAlign: 'right',
        labels: { colors: '#fff' }
    },
    theme: { mode: 'dark' },
    colors: ['#405189', '#0ab39c', '#f7b84b'],
    tooltip: {
        shared: true,
        intersect: false,
        y: {
            formatter: function (y: number, { seriesIndex }: any) {
                if (typeof y !== "undefined") {
                    if (seriesIndex === 0) return y.toFixed(0) + " orders";
                    return formatCurrency(y);
                }
                return y;
            }
        }
    }
}))

const chartSeries = computed(() => [
    {
        name: 'Orders',
        type: 'column',
        data: sampleChartData.value.map(d => d.count)
    },
    {
        name: 'Earnings',
        type: 'area', // Area gives that nice glow below the line
        data: sampleChartData.value.map(d => d.total)
    },
    {
        name: 'Refunds',
        type: 'line',
        data: sampleChartData.value.map(d => Math.floor(d.count * 0.1 * Math.random() * 1000000)) // Fake refund data for mockup
    }
])

onMounted(fetchAnalytics)
</script>

<template>
    <div class="min-h-screen p-2">
        <!-- Welcome Header -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500 p-8 mb-8">
            <div class="absolute inset-0 opacity-30">
                <div class="absolute top-4 left-8 w-32 h-32 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute bottom-4 right-12 w-48 h-48 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute top-1/2 left-1/3 w-24 h-24 bg-pink-300/20 rounded-full blur-2xl"></div>
            </div>
            <div class="relative z-10 flex flex-wrap items-center justify-between gap-6">
                <div>
                    <h1 class="text-4xl font-bold text-white mb-2">Xin chào, Admin! 👋</h1>
                    <p class="text-white/80 text-lg">{{ currentTime.toLocaleDateString('vi-VN', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) }} • {{ formatTime(currentTime) }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex bg-white/10 backdrop-blur-sm rounded-xl p-1 border border-white/10">
                        <button v-for="range in dateRanges" :key="range"
                            @click="handleRangeChange(range)"
                            :class="[
                                'px-4 py-1.5 rounded-lg text-xs font-bold transition-all duration-200',
                                dateRange === range
                                    ? 'bg-white text-indigo-600 shadow-sm'
                                    : 'text-white/70 hover:text-white hover:bg-white/5'
                            ]">
                            {{ range === '7d' ? '7 ngày' : range === '30d' ? '30 ngày' : '90 ngày' }}
                        </button>
                    </div>
                    <button class="btn btn-success">
                        <i class="ri-add-circle-line align-middle me-1"></i> Add Product
                    </button>
                    <button class="btn btn-soft-info btn-icon">
                        <i class="ri-pulse-line"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div v-for="i in 4" :key="i" class="h-40 rounded-3xl bg-dark-700 animate-pulse"></div>
        </div>

        <template v-else>
            <!-- KPI Cards - Glass Design -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Revenue Card -->
                <div class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-500/20 to-teal-500/10 p-6 border border-emerald-500/20 hover:border-emerald-400/40 transition-all duration-500 hover:scale-[1.02] hover:shadow-2xl hover:shadow-emerald-500/20">
                    <div class="absolute -right-8 -top-8 w-32 h-32 bg-emerald-500/20 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-500"></div>
                    <div class="relative">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center shadow-lg shadow-emerald-500/30">
                                <span class="text-2xl">💰</span>
                            </div>
                            <span class="text-emerald-400 text-sm font-medium px-3 py-1 rounded-full bg-emerald-500/20">+12%</span>
                        </div>
                        <h3 class="text-4xl font-bold text-white mb-1">{{ formatCompact(kpis.revenue) }}</h3>
                        <p class="text-slate-400 font-medium">Tổng doanh thu</p>
                    </div>
                </div>

                <!-- Orders Card -->
                <div class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-500/20 to-indigo-500/10 p-6 border border-blue-500/20 hover:border-blue-400/40 transition-all duration-500 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/20">
                    <div class="absolute -right-8 -top-8 w-32 h-32 bg-blue-500/20 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-500"></div>
                    <div class="relative">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center shadow-lg shadow-blue-500/30">
                                <span class="text-2xl">📦</span>
                            </div>
                            <span class="text-blue-400 text-sm font-medium px-3 py-1 rounded-full bg-blue-500/20">+8%</span>
                        </div>
                        <h3 class="text-4xl font-bold text-white mb-1">{{ formatNumber(kpis.order_count) }}</h3>
                        <p class="text-slate-400 font-medium">Đơn hàng</p>
                    </div>
                </div>

                <!-- AOV Card -->
                <div class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-purple-500/20 to-pink-500/10 p-6 border border-purple-500/20 hover:border-purple-400/40 transition-all duration-500 hover:scale-[1.02] hover:shadow-2xl hover:shadow-purple-500/20">
                    <div class="absolute -right-8 -top-8 w-32 h-32 bg-purple-500/20 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-500"></div>
                    <div class="relative">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-400 to-pink-500 flex items-center justify-center shadow-lg shadow-purple-500/30">
                                <span class="text-2xl">📊</span>
                            </div>
                            <span class="text-purple-400 text-sm font-medium px-3 py-1 rounded-full bg-purple-500/20">+5%</span>
                        </div>
                        <h3 class="text-4xl font-bold text-white mb-1">{{ formatCompact(kpis.avg_order_value) }}</h3>
                        <p class="text-slate-400 font-medium">Giá trị TB/đơn</p>
                    </div>
                </div>

                <!-- Customers Card -->
                <div class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-orange-500/20 to-amber-500/10 p-6 border border-orange-500/20 hover:border-orange-400/40 transition-all duration-500 hover:scale-[1.02] hover:shadow-2xl hover:shadow-orange-500/20">
                    <div class="absolute -right-8 -top-8 w-32 h-32 bg-orange-500/20 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-500"></div>
                    <div class="relative">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-orange-400 to-amber-500 flex items-center justify-center shadow-lg shadow-orange-500/30">
                                <span class="text-2xl">👥</span>
                            </div>
                            <span class="text-orange-400 text-sm font-medium px-3 py-1 rounded-full bg-orange-500/20">+{{ kpis.new_customers }}</span>
                        </div>
                        <h3 class="text-4xl font-bold text-white mb-1">{{ formatNumber(kpis.customer_count) }}</h3>
                        <p class="text-slate-400 font-medium">Khách hàng</p>
                    </div>
                </div>
            </div>

            <!-- Alerts -->
            <div v-if="alerts.pending_orders > 0 || alerts.low_stock > 0" 
                class="mb-8 p-6 rounded-3xl bg-gradient-to-r from-amber-500/10 via-orange-500/10 to-red-500/10 border border-amber-500/30 backdrop-blur-sm">
                <div class="flex flex-wrap items-center gap-6">
                    <div class="flex items-center gap-3">
                        <span class="text-3xl animate-bounce">⚠️</span>
                        <span class="font-bold text-white text-lg">Cần chú ý</span>
                    </div>
                    <div class="flex flex-wrap gap-4">
                        <div v-if="alerts.pending_orders > 0" 
                            class="flex items-center gap-3 px-5 py-2.5 rounded-2xl bg-amber-500/20 border border-amber-500/40">
                            <span class="w-3 h-3 rounded-full bg-amber-400 animate-pulse"></span>
                            <span class="text-amber-300 font-bold text-lg">{{ alerts.pending_orders }}</span>
                            <span class="text-amber-200">đơn chờ xử lý</span>
                        </div>
                        <div v-if="alerts.low_stock > 0"
                            class="flex items-center gap-3 px-5 py-2.5 rounded-2xl bg-red-500/20 border border-red-500/40">
                            <span class="w-3 h-3 rounded-full bg-red-400 animate-pulse"></span>
                            <span class="text-red-300 font-bold text-lg">{{ alerts.low_stock }}</span>
                            <span class="text-red-200">sản phẩm sắp hết</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue Chart Section (Velzon Style) -->
            <div class="rounded-3xl bg-dark-800 border border-white/10 overflow-hidden mb-8 backdrop-blur-md">
                <!-- Header with Summary Stats -->
                <div class="p-8 border-b border-white/5">
                    <div class="flex flex-wrap items-center justify-between gap-6 mb-8">
                        <div>
                            <h2 class="text-2xl font-bold text-white flex items-center gap-3">
                                <span class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-lg">📊</span>
                                Revenue & Orders
                            </h2>
                            <p class="text-slate-400 mt-1">Xu hướng kinh doanh và hiệu quả vận hành</p>
                        </div>
                        <div class="flex gap-1">
                            <button v-for="t in chartPeriods" :key="t"
                                class="btn btn-sm btn-ghost-secondary px-3 py-1 text-xs font-bold transition-all"
                                :class="{ 'btn-soft-info active': t === '1M' }">
                                {{ t }}
                            </button>
                        </div>
                    </div>

                    <!-- Summary Metrics Row -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 py-6 px-4 bg-slate-900/50 rounded-2xl border border-white/5">
                        <div class="text-center md:border-r border-white/10 last:border-0">
                            <div class="text-2xl font-bold text-white mb-1">{{ formatNumber(sampleChartData.reduce((a, b) => a + (b.count || 0), 0)) }}</div>
                            <div class="text-xs text-slate-500 uppercase tracking-wider font-semibold">Orders</div>
                        </div>
                        <div class="text-center md:border-r border-white/10 last:border-0">
                            <div class="text-2xl font-bold text-white mb-1">{{ formatCompact(sampleChartData.reduce((a, b) => a + (parseFloat(String(b.total)) || 0), 0)) }}</div>
                            <div class="text-xs text-slate-500 uppercase tracking-wider font-semibold">Earnings</div>
                        </div>
                        <div class="text-center md:border-r border-white/10 last:border-0">
                            <div class="text-2xl font-bold text-white mb-1">{{ formatNumber(Math.floor(sampleChartData.length * 1.5)) }}</div>
                            <div class="text-xs text-slate-500 uppercase tracking-wider font-semibold">Refunds</div>
                        </div>
                        <div class="text-center last:border-0">
                            <div class="text-2xl font-bold text-emerald-400 mb-1">18.92%</div>
                            <div class="text-xs text-slate-500 uppercase tracking-wider font-semibold">Conv. Ratio</div>
                        </div>
                    </div>
                </div>

                <!-- Apex Chart -->
                <div class="p-4 md:p-8 bg-dark-800/50">
                    <apexchart 
                        type="line" 
                        height="350" 
                        :options="chartOptions" 
                        :series="chartSeries"
                    ></apexchart>
                </div>
            </div>

            <!-- Grid Section -->
            <div class="grid lg:grid-cols-2 gap-8">
                <!-- Recent Orders -->
                <div class="rounded-3xl bg-gradient-to-br from-slate-800/50 to-slate-900/50 border border-white/10 p-6 backdrop-blur-sm">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-white flex items-center gap-3">
                            <span class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center">📋</span>
                            Đơn hàng gần đây
                        </h3>
                        <router-link to="/admin/orders" class="text-sm text-indigo-400 hover:text-indigo-300 font-medium flex items-center gap-1 group">
                            Xem tất cả <span class="group-hover:translate-x-1 transition-transform">→</span>
                        </router-link>
                    </div>
                    <div class="space-y-3">
                        <div v-for="order in recentOrders.slice(0, 5)" :key="order.id"
                            class="flex items-center justify-between p-4 rounded-2xl bg-white/5 hover:bg-white/10 transition-all duration-300 group cursor-pointer border border-transparent hover:border-white/10">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br flex items-center justify-center text-white font-bold"
                                    :class="getStatusColor(order.status)">
                                    {{ order.order_number?.slice(-2) || '##' }}
                                </div>
                                <div>
                                    <div class="font-semibold text-white group-hover:text-indigo-300 transition-colors">{{ order.order_number }}</div>
                                    <div class="text-sm text-slate-500">{{ order.user?.name || 'Khách vãng lai' }}</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-purple-400">{{ formatCurrency(order.total) }}</div>
                                <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-gradient-to-r text-white"
                                    :class="getStatusColor(order.status)">
                                    {{ getStatusLabel(order.status) }}
                                </span>
                            </div>
                        </div>
                        <div v-if="!recentOrders.length" class="text-center py-12">
                            <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-slate-800 flex items-center justify-center">
                                <span class="text-4xl opacity-50">📦</span>
                            </div>
                            <p class="text-slate-400">Chưa có đơn hàng nào</p>
                            <router-link to="/admin/orders/create" class="text-indigo-400 text-sm hover:underline">+ Tạo đơn hàng mới</router-link>
                        </div>
                    </div>
                </div>

                <!-- Top Products -->
                <div class="rounded-3xl bg-gradient-to-br from-slate-800/50 to-slate-900/50 border border-white/10 p-6 backdrop-blur-sm">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-white flex items-center gap-3">
                            <span class="w-9 h-9 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center">🏆</span>
                            Sản phẩm bán chạy
                        </h3>
                        <router-link to="/admin/products" class="text-sm text-indigo-400 hover:text-indigo-300 font-medium flex items-center gap-1 group">
                            Xem tất cả <span class="group-hover:translate-x-1 transition-transform">→</span>
                        </router-link>
                    </div>
                    <div class="space-y-3">
                        <div v-for="(product, index) in topProducts.slice(0, 5)" :key="product.id"
                            class="flex items-center gap-4 p-4 rounded-2xl bg-white/5 hover:bg-white/10 transition-all duration-300 group cursor-pointer border border-transparent hover:border-white/10">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl"
                                :class="index === 0 ? 'bg-gradient-to-br from-yellow-400 to-amber-600 shadow-lg shadow-amber-500/30' 
                                       : index === 1 ? 'bg-gradient-to-br from-slate-300 to-slate-500' 
                                       : index === 2 ? 'bg-gradient-to-br from-amber-600 to-amber-800' 
                                       : 'bg-slate-700'">
                                {{ index === 0 ? '🥇' : index === 1 ? '🥈' : index === 2 ? '🥉' : '🏅' }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold text-white truncate group-hover:text-amber-300 transition-colors">{{ product.name }}</div>
                                <div class="text-sm text-slate-500">Đã bán: {{ formatNumber(product.total_qty) }}</div>
                            </div>
                            <div class="font-bold text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-orange-400">
                                {{ formatCompact(product.total_value) }}
                            </div>
                        </div>
                        <div v-if="!topProducts.length" class="text-center py-12">
                            <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-slate-800 flex items-center justify-center">
                                <span class="text-4xl opacity-50">🛍️</span>
                            </div>
                            <p class="text-slate-400">Chưa có dữ liệu sản phẩm</p>
                            <router-link to="/admin/products" class="text-indigo-400 text-sm hover:underline">+ Thêm sản phẩm mới</router-link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Stats -->
            <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="rounded-3xl p-6 bg-gradient-to-br from-emerald-600/20 to-teal-600/10 border border-emerald-500/30 hover:border-emerald-400/50 transition-all duration-300 group hover:shadow-xl hover:shadow-emerald-500/10">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-3xl shadow-lg shadow-emerald-500/30">
                            💵
                        </div>
                        <div>
                            <p class="text-slate-400 text-sm font-medium mb-1">Lợi nhuận gộp</p>
                            <h4 class="text-3xl font-bold text-emerald-400">{{ formatCompact(kpis.gross_profit) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="rounded-3xl p-6 bg-gradient-to-br from-indigo-600/20 to-purple-600/10 border border-indigo-500/30 hover:border-indigo-400/50 transition-all duration-300 group hover:shadow-xl hover:shadow-indigo-500/10">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-3xl shadow-lg shadow-indigo-500/30">
                            📈
                        </div>
                        <div>
                            <p class="text-slate-400 text-sm font-medium mb-1">Biên lợi nhuận</p>
                            <h4 class="text-3xl font-bold text-indigo-400">{{ (kpis.gross_margin || 0).toFixed(1) }}%</h4>
                        </div>
                    </div>
                </div>
                <div class="rounded-3xl p-6 bg-gradient-to-br from-pink-600/20 to-rose-600/10 border border-pink-500/30 hover:border-pink-400/50 transition-all duration-300 group hover:shadow-xl hover:shadow-pink-500/10">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-pink-500 to-rose-600 flex items-center justify-center text-3xl shadow-lg shadow-pink-500/30">
                            🎯
                        </div>
                        <div>
                            <p class="text-slate-400 text-sm font-medium mb-1">Tỷ lệ hoàn thành</p>
                            <h4 class="text-3xl font-bold text-pink-400">{{ kpis.order_count > 0 ? '95' : '0' }}%</h4>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<style scoped>
.card {
    @apply rounded-3xl bg-dark-800/50 backdrop-blur-sm border border-white/5;
}
</style>
