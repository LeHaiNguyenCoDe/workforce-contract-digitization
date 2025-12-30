<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'

// Store
const store = useWarehouseStore()
const { t } = useI18n()

// State
const searchQuery = ref('')
const viewMode = ref<'product' | 'batch' | 'location'>('product')
const statusFilter = ref('')
const warehouseFilter = ref<number | null>(null)

// Computed
const isLoading = computed(() => store.isLoading)
const stocks = computed(() => store.stocks || [])
const warehouses = computed(() => store.warehouses || [])

const filteredStocks = computed(() => {
    let result = stocks.value

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        result = result.filter((s: any) =>
            s.product?.name?.toLowerCase().includes(query) ||
            s.product?.sku?.toLowerCase().includes(query) ||
            s.batch_number?.toLowerCase().includes(query)
        )
    }

    if (statusFilter.value) {
        result = result.filter((s: any) => s.status === statusFilter.value)
    }

    if (warehouseFilter.value) {
        result = result.filter((s: any) => s.warehouse_id === warehouseFilter.value)
    }

    return result
})

// Grouped data for different views
const groupedByProduct = computed(() => {
    const groups: Record<number, any> = {}
    filteredStocks.value.forEach((stock: any) => {
        const productId = stock.product_id
        if (!groups[productId]) {
            groups[productId] = {
                product: stock.product,
                totalQty: 0,
                availableQty: 0,
                stocks: []
            }
        }
        groups[productId].totalQty += stock.quantity || 0
        groups[productId].availableQty += stock.available_quantity || 0
        groups[productId].stocks.push(stock)
    })
    return Object.values(groups)
})

const groupedByBatch = computed(() => {
    const groups: Record<string, any> = {}
    filteredStocks.value.forEach((stock: any) => {
        const batchId = stock.inbound_batch_id || 'no-batch'
        if (!groups[batchId]) {
            groups[batchId] = {
                batch: stock.inbound_batch,
                batchNumber: stock.inbound_batch?.batch_number || t('admin.noBatch'),
                totalQty: 0,
                availableQty: 0,
                stocks: []
            }
        }
        groups[batchId].totalQty += stock.quantity || 0
        groups[batchId].availableQty += stock.available_quantity || 0
        groups[batchId].stocks.push(stock)
    })
    return Object.values(groups)
})

const groupedByLocation = computed(() => {
    const groups: Record<string, any> = {}
    filteredStocks.value.forEach((stock: any) => {
        const warehouseId = stock.warehouse_id
        const warehouseName = stock.warehouse?.name || t('admin.unknown')
        if (!groups[warehouseId]) {
            groups[warehouseId] = {
                warehouse: stock.warehouse,
                warehouseName,
                totalQty: 0,
                availableQty: 0,
                stocks: []
            }
        }
        groups[warehouseId].totalQty += stock.quantity || 0
        groups[warehouseId].availableQty += stock.available_quantity || 0
        groups[warehouseId].stocks.push(stock)
    })
    return Object.values(groups)
})

const currentGroupedData = computed(() => {
    switch (viewMode.value) {
        case 'batch': return groupedByBatch.value
        case 'location': return groupedByLocation.value
        default: return groupedByProduct.value
    }
})

// Methods
const getStatusClass = (status: string) => {
    switch (status) {
        case 'available': return 'bg-success/10 text-success'
        case 'damaged': return 'bg-error/10 text-error'
        case 'hold': return 'bg-warning/10 text-warning'
        case 'reserved': return 'bg-info/10 text-info'
        default: return 'bg-slate-500/10 text-slate-400'
    }
}

const getStatusLabel = (status: string) => {
    switch (status) {
        case 'available': return t('admin.available')
        case 'damaged': return t('admin.damaged')
        case 'hold': return t('admin.hold')
        case 'reserved': return t('admin.reserved')
        default: return t('admin.available')
    }
}

const formatDate = (dateString: string | null) => {
    if (!dateString) return '-'
    try {
        const date = new Date(dateString)
        return date.toLocaleDateString('vi-VN')
    } catch {
        return dateString
    }
}

// Lifecycle
onMounted(async () => {
    await store.fetchWarehouses()
    await store.fetchAllStocks()
})
</script>

<template>
    <div class="h-full flex flex-col p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6 flex-shrink-0">
            <div>
                <h1 class="text-2xl font-bold text-white">{{ t('admin.inventoryManagement') }}</h1>
                <p class="text-slate-400 mt-1">{{ t('admin.inventoryDesc') }}</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-dark-800 rounded-xl border border-white/10 p-4 mb-6 flex-shrink-0">
            <div class="flex flex-wrap gap-4">
                <!-- View Mode Tabs -->
                <div class="flex bg-dark-700 rounded-lg p-1">
                    <button @click="viewMode = 'product'" :class="['px-4 py-2 rounded-md text-sm font-medium transition-colors',
                        viewMode === 'product' ? 'bg-primary text-white' : 'text-slate-400 hover:text-white']">
                        {{ t('admin.byProduct') }}
                    </button>
                    <button @click="viewMode = 'batch'" :class="['px-4 py-2 rounded-md text-sm font-medium transition-colors',
                        viewMode === 'batch' ? 'bg-primary text-white' : 'text-slate-400 hover:text-white']">
                        {{ t('admin.byBatch') }}
                    </button>
                    <button @click="viewMode = 'location'" :class="['px-4 py-2 rounded-md text-sm font-medium transition-colors',
                        viewMode === 'location' ? 'bg-primary text-white' : 'text-slate-400 hover:text-white']">
                        {{ t('admin.byLocation') }}
                    </button>
                </div>

                <!-- Search -->
                <div class="relative flex-1 min-w-[200px]">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"
                        xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.3-4.3" />
                    </svg>
                    <input v-model="searchQuery" type="text" :placeholder="t('common.searchPlaceholder')"
                        class="w-full pl-10 pr-4 py-2.5 bg-dark-700 border border-white/10 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary/50" />
                </div>

                <!-- Warehouse Filter -->
                <select v-model="warehouseFilter"
                    class="px-4 py-2.5 bg-dark-700 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary/50">
                    <option :value="null">{{ t('admin.allWarehouses') }}</option>
                    <option v-for="wh in warehouses" :key="wh.id" :value="wh.id">{{ wh.name }}</option>
                </select>

                <!-- Status Filter -->
                <select v-model="statusFilter"
                    class="px-4 py-2.5 bg-dark-700 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary/50">
                    <option value="">{{ t('common.allStatuses') }}</option>
                    <option value="available">{{ t('admin.available') }}</option>
                    <option value="damaged">{{ t('admin.damaged') }}</option>
                    <option value="hold">{{ t('admin.hold') }}</option>
                    <option value="reserved">{{ t('admin.reserved') }}</option>
                </select>
            </div>
        </div>

        <!-- Content -->
        <div class="flex-1 min-h-0 bg-dark-800 rounded-xl border border-white/10 overflow-hidden flex flex-col">
            <div v-if="isLoading" class="flex-1 flex items-center justify-center">
                <div class="inline-block w-8 h-8 border-4 border-primary/20 border-t-primary rounded-full animate-spin">
                </div>
            </div>

            <div v-else class="flex-1 overflow-auto">
                <!-- Grouped Cards -->
                <div class="p-4 space-y-4">
                    <div v-for="(group, index) in currentGroupedData" :key="index"
                        class="bg-dark-700 rounded-xl border border-white/10 overflow-hidden">
                        <!-- Group Header -->
                        <div class="flex items-center justify-between p-4 bg-dark-600 border-b border-white/10">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center">
                                    <svg v-if="viewMode === 'product'" xmlns="http://www.w3.org/2000/svg" width="24"
                                        height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" class="text-primary">
                                        <path d="m7.5 4.27 9 5.15" />
                                        <path
                                            d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" />
                                        <path d="m3.3 7 8.7 5 8.7-5" />
                                        <path d="M12 22V12" />
                                    </svg>
                                    <svg v-else-if="viewMode === 'batch'" xmlns="http://www.w3.org/2000/svg" width="24"
                                        height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" class="text-primary">
                                        <path
                                            d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" />
                                    </svg>
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        class="text-primary">
                                        <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                                        <polyline points="9 22 9 12 15 12 15 22" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white">
                                        {{ viewMode === 'product' ? group.product?.name :
                                            viewMode === 'batch' ? group.batchNumber :
                                                group.warehouseName }}
                                    </h3>
                                    <p class="text-sm text-slate-400">
                                        {{ group.stocks?.length || 0 }} {{ t('admin.inventoryLines') }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-6">
                                <div class="text-right">
                                    <p class="text-sm text-slate-400">{{ t('admin.totalStock') }}</p>
                                    <p class="text-xl font-bold text-white">{{ group.totalQty }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-slate-400">{{ t('admin.availableStock') }}</p>
                                    <p class="text-xl font-bold text-success">{{ group.availableQty }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Group Details -->
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-dark-800">
                                    <tr class="border-b border-white/5">
                                        <th v-if="viewMode !== 'product'"
                                            class="px-4 py-3 text-left text-xs font-semibold text-slate-400">{{
                                            t('admin.products') }}
                                        </th>
                                        <th v-if="viewMode !== 'batch'"
                                            class="px-4 py-3 text-left text-xs font-semibold text-slate-400">{{
                                            t('admin.batch') }}</th>
                                        <th v-if="viewMode !== 'location'"
                                            class="px-4 py-3 text-left text-xs font-semibold text-slate-400">{{
                                            t('admin.warehouse') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400">{{
                                            t('common.quantity') }}
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400">{{
                                            t('admin.availableStock') }}
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400">{{
                                            t('admin.expiryDate') }}
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400">{{
                                            t('common.status') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/5">
                                    <tr v-for="stock in group.stocks" :key="stock.id" class="hover:bg-white/5">
                                        <td v-if="viewMode !== 'product'" class="px-4 py-3 text-sm text-white">
                                            {{ stock.product?.name || '-' }}
                                        </td>
                                        <td v-if="viewMode !== 'batch'" class="px-4 py-3 text-sm text-slate-300">
                                            {{ stock.inbound_batch?.batch_number || '-' }}
                                        </td>
                                        <td v-if="viewMode !== 'location'" class="px-4 py-3 text-sm text-slate-300">
                                            {{ stock.warehouse?.name || '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm font-medium text-white">
                                            {{ stock.quantity || 0 }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span
                                                class="px-2 py-1 rounded-full text-xs font-medium bg-success/10 text-success">
                                                {{ stock.available_quantity || 0 }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-slate-400">
                                            {{ formatDate(stock.expiry_date) }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span
                                                :class="['px-2 py-1 rounded-full text-xs font-medium', getStatusClass(stock.status || 'available')]">
                                                {{ getStatusLabel(stock.status || 'available') }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div v-if="!currentGroupedData.length" class="py-16 text-center">
                        <p class="text-slate-400">{{ t('admin.noInventoryData') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
