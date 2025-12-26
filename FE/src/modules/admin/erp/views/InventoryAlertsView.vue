<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useSwal } from '@/shared/utils'
import BaseModal from '@/shared/components/BaseModal.vue'
import httpClient from '@/plugins/api/httpClient'

interface Product {
    id: number
    name: string
    sku: string
    thumbnail?: string
}

interface Warehouse {
    id: number
    name: string
}

interface AlertItem {
    setting_id?: number
    batch_id?: number
    batch_code?: string
    product: Product
    warehouse: Warehouse | null
    current_stock: number
    min_quantity?: number
    max_quantity?: number
    shortage?: number
    excess?: number
    recommended_order?: number
    auto_create?: boolean
    remaining_quantity?: number
    expiry_date?: string
    days_until_expiry?: number
}

interface InventorySetting {
    id: number
    product_id: number
    warehouse_id: number | null
    min_quantity: number
    max_quantity: number
    reorder_quantity: number
    auto_create_purchase_request: boolean
    product?: Product
    warehouse?: Warehouse
}

interface Alerts {
    low_stock: AlertItem[]
    over_stock: AlertItem[]
    expiring_soon: AlertItem[]
}

const swal = useSwal()

// State
const alerts = ref<Alerts>({
    low_stock: [],
    over_stock: [],
    expiring_soon: []
})
const settings = ref<InventorySetting[]>([])
const products = ref<Product[]>([])
const warehouses = ref<Warehouse[]>([])
const isLoading = ref(true)
const isSaving = ref(false)
const activeTab = ref<'alerts' | 'settings'>('alerts')
const showSettingModal = ref(false)
const editingSetting = ref<InventorySetting | null>(null)
const form = ref({
    product_id: null as number | null,
    warehouse_id: null as number | null,
    min_quantity: 0,
    max_quantity: 0,
    reorder_quantity: 0,
    auto_create_purchase_request: false
})

// Computed
const alertSummary = computed(() => ({
    low_stock: alerts.value.low_stock.length,
    over_stock: alerts.value.over_stock.length,
    expiring_soon: alerts.value.expiring_soon.length,
    total: alerts.value.low_stock.length + alerts.value.over_stock.length + alerts.value.expiring_soon.length
}))

// Methods
async function fetchAlerts() {
    try {
        const response = await httpClient.get('/admin/inventory/alerts')
        const data = response.data as any
        if (data.status === 'success') {
            alerts.value = data.data
        }
    } catch (error: any) {
        await swal.error(error.response?.data?.message || 'Lỗi tải cảnh báo')
    }
}

async function fetchSettings() {
    try {
        const response = await httpClient.get('/admin/inventory/settings')
        const data = response.data as any
        if (data.status === 'success') {
            settings.value = data.data
        }
    } catch (error: any) {
        await swal.error(error.response?.data?.message || 'Lỗi tải cài đặt')
    }
}

async function fetchProducts() {
    try {
        const response = await httpClient.get('/admin/products?per_page=100')
        products.value = (response.data as any).data || []
    } catch (error) {
        console.error('Error fetching products', error)
    }
}

async function fetchWarehouses() {
    try {
        const response = await httpClient.get('/admin/warehouses')
        warehouses.value = (response.data as any).data || []
    } catch (error) {
        console.error('Error fetching warehouses', error)
    }
}

function openCreateModal() {
    editingSetting.value = null
    form.value = {
        product_id: null,
        warehouse_id: null,
        min_quantity: 0,
        max_quantity: 0,
        reorder_quantity: 0,
        auto_create_purchase_request: false
    }
    showSettingModal.value = true
}

function openEditModal(setting: InventorySetting) {
    editingSetting.value = setting
    form.value = {
        product_id: setting.product_id,
        warehouse_id: setting.warehouse_id,
        min_quantity: setting.min_quantity,
        max_quantity: setting.max_quantity,
        reorder_quantity: setting.reorder_quantity,
        auto_create_purchase_request: setting.auto_create_purchase_request
    }
    showSettingModal.value = true
}

async function saveSetting() {
    if (!form.value.product_id) {
        await swal.warning('Vui lòng chọn sản phẩm')
        return
    }

    isSaving.value = true
    try {
        await httpClient.post('/admin/inventory/settings', form.value)
        await swal.success('Lưu cài đặt thành công')
        showSettingModal.value = false
        await Promise.all([fetchSettings(), fetchAlerts()])
    } catch (error: any) {
        await swal.error(error.response?.data?.message || 'Lỗi lưu cài đặt')
    } finally {
        isSaving.value = false
    }
}

async function deleteSetting(setting: InventorySetting) {
    const confirmed = await swal.confirm(`Xóa cài đặt cho ${setting.product?.name}?`)
    if (!confirmed) return

    try {
        await httpClient.delete(`/admin/inventory/settings/${setting.id}`)
        await swal.success('Đã xóa cài đặt')
        await fetchSettings()
    } catch (error: any) {
        await swal.error(error.response?.data?.message || 'Lỗi xóa')
    }
}

async function createPurchaseRequest(alert: AlertItem) {
    const confirmed = await swal.confirm(
        `Sản phẩm: ${alert.product.name}\nSố lượng đề xuất: ${alert.recommended_order}`,
        'Tạo phiếu đề nghị nhập hàng?'
    )
    if (!confirmed) return

    try {
        await httpClient.post('/admin/purchase-requests', {
            product_id: alert.product.id,
            warehouse_id: alert.warehouse?.id || null,
            requested_quantity: alert.recommended_order || alert.shortage,
            current_stock: alert.current_stock,
            min_stock: alert.min_quantity,
            notes: 'Tạo từ cảnh báo tồn kho thấp'
        })
        await swal.success('Đã tạo phiếu đề nghị')
    } catch (error: any) {
        await swal.error(error.response?.data?.message || 'Lỗi tạo phiếu')
    }
}

async function triggerAutoRequests() {
    try {
        const response = await httpClient.post('/admin/inventory/alerts/trigger-requests')
        const data = response.data as any
        await swal.success(data.message || 'Đã kiểm tra và tạo phiếu tự động')
        await fetchAlerts()
    } catch (error: any) {
        await swal.error(error.response?.data?.message || 'Lỗi')
    }
}

function getExpiryClass(days: number | undefined): string {
    if (days === undefined) return ''
    if (days <= 7) return 'text-red-400'
    if (days <= 14) return 'text-orange-400'
    return 'text-yellow-400'
}

// Lifecycle
onMounted(async () => {
    await Promise.all([fetchAlerts(), fetchSettings(), fetchProducts(), fetchWarehouses()])
    isLoading.value = false
})
</script>

<template>
    <div class="h-full flex flex-col p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6 flex-shrink-0">
            <div>
                <h1 class="text-2xl font-bold text-white">Cảnh báo tồn kho</h1>
                <p class="text-slate-400 mt-1">Giám sát min/max và hạn sử dụng sản phẩm</p>
            </div>
            <div class="flex items-center gap-3">
                <button @click="triggerAutoRequests"
                    class="px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white rounded-lg transition-colors">
                    ⚡ Tạo phiếu tự động
                </button>
                <button @click="openCreateModal"
                    class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="M12 5v14M5 12h14" />
                    </svg>
                    Thêm cài đặt
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-4 gap-4 mb-6 flex-shrink-0">
            <div class="bg-red-500/20 border border-red-500/30 rounded-xl p-4">
                <div class="text-3xl font-bold text-red-400">{{ alertSummary.low_stock }}</div>
                <div class="text-red-300/80 text-sm">Dưới mức tối thiểu</div>
            </div>
            <div class="bg-orange-500/20 border border-orange-500/30 rounded-xl p-4">
                <div class="text-3xl font-bold text-orange-400">{{ alertSummary.over_stock }}</div>
                <div class="text-orange-300/80 text-sm">Vượt mức tối đa</div>
            </div>
            <div class="bg-yellow-500/20 border border-yellow-500/30 rounded-xl p-4">
                <div class="text-3xl font-bold text-yellow-400">{{ alertSummary.expiring_soon }}</div>
                <div class="text-yellow-300/80 text-sm">Sắp hết hạn</div>
            </div>
            <div class="bg-slate-700/50 border border-slate-600/50 rounded-xl p-4">
                <div class="text-3xl font-bold text-white">{{ settings.length }}</div>
                <div class="text-slate-400 text-sm">Cài đặt ngưỡng</div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="flex gap-2 mb-4 flex-shrink-0">
            <button @click="activeTab = 'alerts'"
                :class="['px-4 py-2 rounded-lg transition-colors', activeTab === 'alerts' ? 'bg-primary text-white' : 'bg-dark-700 text-slate-400 hover:text-white']">
                Cảnh báo
            </button>
            <button @click="activeTab = 'settings'"
                :class="['px-4 py-2 rounded-lg transition-colors', activeTab === 'settings' ? 'bg-primary text-white' : 'bg-dark-700 text-slate-400 hover:text-white']">
                Cài đặt ngưỡng
            </button>
        </div>

        <!-- Content -->
        <div class="flex-1 min-h-0 bg-dark-800 rounded-xl border border-white/10 overflow-hidden">
            <div v-if="isLoading" class="h-full flex items-center justify-center">
                <div class="animate-spin rounded-full h-12 w-12 border-4 border-primary border-t-transparent"></div>
            </div>

            <!-- Alerts Tab -->
            <div v-else-if="activeTab === 'alerts'" class="h-full overflow-auto p-4 space-y-6">
                <!-- Low Stock -->
                <div v-if="alerts.low_stock.length > 0">
                    <h3 class="text-lg font-semibold text-red-400 mb-3 flex items-center gap-2">
                        <span class="text-2xl">⚠️</span> Tồn kho thấp
                    </h3>
                    <div class="space-y-2">
                        <div v-for="alert in alerts.low_stock" :key="`low-${alert.setting_id}`"
                            class="bg-red-500/10 border border-red-500/20 rounded-lg p-4 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-dark-700 rounded-lg flex items-center justify-center text-2xl">
                                    📦
                                </div>
                                <div>
                                    <div class="font-semibold text-white">{{ alert.product.name }}</div>
                                    <div class="text-sm text-slate-400">{{ alert.product.sku }}
                                        <span v-if="alert.warehouse">· {{ alert.warehouse.name }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-6">
                                <div class="text-center">
                                    <div class="text-red-400 font-bold text-xl">{{ alert.current_stock }}</div>
                                    <div class="text-xs text-slate-500">Hiện tại</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-white font-bold text-xl">{{ alert.min_quantity }}</div>
                                    <div class="text-xs text-slate-500">Tối thiểu</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-red-400 font-bold text-xl">-{{ alert.shortage }}</div>
                                    <div class="text-xs text-slate-500">Thiếu</div>
                                </div>
                                <button @click="createPurchaseRequest(alert)"
                                    class="px-3 py-1.5 bg-primary hover:bg-primary-dark text-white text-sm rounded-lg transition-colors">
                                    + Tạo phiếu nhập
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Over Stock -->
                <div v-if="alerts.over_stock.length > 0">
                    <h3 class="text-lg font-semibold text-orange-400 mb-3 flex items-center gap-2">
                        <span class="text-2xl">📈</span> Tồn kho vượt mức
                    </h3>
                    <div class="space-y-2">
                        <div v-for="alert in alerts.over_stock" :key="`over-${alert.setting_id}`"
                            class="bg-orange-500/10 border border-orange-500/20 rounded-lg p-4 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-dark-700 rounded-lg flex items-center justify-center text-2xl">
                                    📦
                                </div>
                                <div>
                                    <div class="font-semibold text-white">{{ alert.product.name }}</div>
                                    <div class="text-sm text-slate-400">{{ alert.product.sku }}
                                        <span v-if="alert.warehouse">· {{ alert.warehouse.name }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-6">
                                <div class="text-center">
                                    <div class="text-orange-400 font-bold text-xl">{{ alert.current_stock }}</div>
                                    <div class="text-xs text-slate-500">Hiện tại</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-white font-bold text-xl">{{ alert.max_quantity }}</div>
                                    <div class="text-xs text-slate-500">Tối đa</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-orange-400 font-bold text-xl">+{{ alert.excess }}</div>
                                    <div class="text-xs text-slate-500">Dư</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Expiring Soon -->
                <div v-if="alerts.expiring_soon.length > 0">
                    <h3 class="text-lg font-semibold text-yellow-400 mb-3 flex items-center gap-2">
                        <span class="text-2xl">⏰</span> Sắp hết hạn
                    </h3>
                    <div class="space-y-2">
                        <div v-for="alert in alerts.expiring_soon" :key="`exp-${alert.batch_id}`"
                            class="bg-yellow-500/10 border border-yellow-500/20 rounded-lg p-4 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-dark-700 rounded-lg flex items-center justify-center text-2xl">
                                    🕐
                                </div>
                                <div>
                                    <div class="font-semibold text-white">{{ alert.product.name }}</div>
                                    <div class="text-sm text-slate-400">
                                        Lô: <span class="font-mono text-primary">{{ alert.batch_code }}</span>
                                        <span v-if="alert.warehouse">· {{ alert.warehouse.name }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-6">
                                <div class="text-center">
                                    <div class="text-white font-bold text-xl">{{ alert.remaining_quantity }}</div>
                                    <div class="text-xs text-slate-500">Số lượng</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-yellow-400 font-semibold">{{ alert.expiry_date }}</div>
                                    <div class="text-xs text-slate-500">Hạn sử dụng</div>
                                </div>
                                <div class="text-center">
                                    <div :class="['font-bold text-xl', getExpiryClass(alert.days_until_expiry)]">
                                        {{ alert.days_until_expiry }} ngày
                                    </div>
                                    <div class="text-xs text-slate-500">Còn lại</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- No Alerts -->
                <div v-if="alertSummary.total === 0" class="h-full flex items-center justify-center">
                    <div class="text-center">
                        <div class="text-6xl mb-4">✅</div>
                        <h3 class="text-xl font-semibold text-white mb-2">Không có cảnh báo</h3>
                        <p class="text-slate-400">Tất cả sản phẩm đều trong ngưỡng an toàn</p>
                    </div>
                </div>
            </div>

            <!-- Settings Tab -->
            <div v-else-if="activeTab === 'settings'" class="h-full overflow-auto">
                <table class="w-full">
                    <thead class="bg-dark-700 sticky top-0">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Sản phẩm</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Kho</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-slate-400 uppercase">Min</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-slate-400 uppercase">Max</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-slate-400 uppercase">SL đặt</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-slate-400 uppercase">Tự động tạo
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-400 uppercase">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="setting in settings" :key="setting.id" class="hover:bg-white/5">
                            <td class="px-4 py-3">
                                <div class="font-medium text-white">{{ setting.product?.name }}</div>
                                <div class="text-xs text-slate-500">{{ setting.product?.sku }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-300">{{ setting.warehouse?.name || 'Tất cả kho' }}</td>
                            <td class="px-4 py-3 text-center text-white">{{ setting.min_quantity }}</td>
                            <td class="px-4 py-3 text-center text-white">{{ setting.max_quantity }}</td>
                            <td class="px-4 py-3 text-center text-white">{{ setting.reorder_quantity }}</td>
                            <td class="px-4 py-3 text-center">
                                <span v-if="setting.auto_create_purchase_request"
                                    class="px-2 py-0.5 bg-green-500/20 text-green-400 rounded text-xs">Bật</span>
                                <span v-else class="px-2 py-0.5 bg-slate-700 text-slate-400 rounded text-xs">Tắt</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openEditModal(setting)"
                                        class="p-2 hover:bg-white/10 rounded-lg text-slate-400 hover:text-white transition-colors">
                                        ✏️
                                    </button>
                                    <button @click="deleteSetting(setting)"
                                        class="p-2 hover:bg-red-500/20 rounded-lg text-slate-400 hover:text-red-400 transition-colors">
                                        🗑️
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="settings.length === 0">
                            <td colspan="7" class="px-4 py-8 text-center text-slate-400">
                                Chưa có cài đặt ngưỡng nào. Nhấn "Thêm cài đặt" để bắt đầu.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Setting Modal -->
        <BaseModal v-model="showSettingModal" :title="editingSetting ? 'Sửa cài đặt ngưỡng' : 'Thêm cài đặt ngưỡng'">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Sản phẩm *</label>
                    <select v-model="form.product_id" :disabled="!!editingSetting"
                        class="w-full px-3 py-2 bg-dark-700 border border-white/10 rounded-lg text-white focus:outline-none focus:border-primary">
                        <option :value="null">Chọn sản phẩm</option>
                        <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }} ({{ p.sku }})</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Kho</label>
                    <select v-model="form.warehouse_id" :disabled="!!editingSetting"
                        class="w-full px-3 py-2 bg-dark-700 border border-white/10 rounded-lg text-white focus:outline-none focus:border-primary">
                        <option :value="null">Tất cả kho</option>
                        <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                    </select>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Tồn tối thiểu</label>
                        <input type="number" v-model.number="form.min_quantity" min="0"
                            class="w-full px-3 py-2 bg-dark-700 border border-white/10 rounded-lg text-white focus:outline-none focus:border-primary" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Tồn tối đa</label>
                        <input type="number" v-model.number="form.max_quantity" min="0"
                            class="w-full px-3 py-2 bg-dark-700 border border-white/10 rounded-lg text-white focus:outline-none focus:border-primary" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">SL đề xuất nhập</label>
                        <input type="number" v-model.number="form.reorder_quantity" min="0"
                            class="w-full px-3 py-2 bg-dark-700 border border-white/10 rounded-lg text-white focus:outline-none focus:border-primary" />
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <input type="checkbox" id="autoCreate" v-model="form.auto_create_purchase_request"
                        class="w-4 h-4 rounded border-white/20 bg-dark-700 text-primary focus:ring-primary" />
                    <label for="autoCreate" class="text-slate-300">Tự động tạo phiếu đề nghị khi dưới mức tối
                        thiểu</label>
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end gap-3">
                    <button @click="showSettingModal = false"
                        class="px-4 py-2 bg-dark-700 hover:bg-dark-600 text-white rounded-lg transition-colors">
                        Hủy
                    </button>
                    <button @click="saveSetting" :disabled="isSaving"
                        class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors disabled:opacity-50">
                        {{ isSaving ? 'Đang lưu...' : 'Lưu cài đặt' }}
                    </button>
                </div>
            </template>
        </BaseModal>
    </div>
</template>
