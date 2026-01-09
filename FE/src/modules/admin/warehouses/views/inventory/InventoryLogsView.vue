<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import httpClient from '@/plugins/api/httpClient'
import { warehouseService } from '@/plugins/api/services/WarehouseService'
import { useSwal } from '@/utils'
import type { InventoryLog } from '@/plugins/api/services/WarehouseService'

const swal = useSwal()

const logs = ref<InventoryLog[]>([])
const warehouses = ref<any[]>([])
const selectedWarehouseId = ref<number | null>(null)
const isLoading = ref(true)
const currentPage = ref(1)
const perPage = ref(20)
const totalPages = ref(1)
const total = ref(0)

const movementTypeConfig: Record<string, { text: string; class: string }> = {
  inbound: { text: 'Nhập kho', class: 'bg-success/10 text-success' },
  qc_pass: { text: 'QC PASS', class: 'bg-success/10 text-success' },
  qc_fail: { text: 'QC FAIL', class: 'bg-error/10 text-error' },
  outbound: { text: 'Xuất kho', class: 'bg-warning/10 text-warning' },
  adjust: { text: 'Điều chỉnh', class: 'bg-info/10 text-info' },
  return: { text: 'Trả hàng', class: 'bg-slate-500/10 text-slate-400' }
}

const fetchWarehouses = async () => {
  try {
    const response = await httpClient.get('/admin/warehouses')
    warehouses.value = (response.data as any).data?.data || (response.data as any).data || []
    if (warehouses.value.length > 0 && !selectedWarehouseId.value) {
      selectedWarehouseId.value = warehouses.value[0].id
    }
  } catch (error) {
    console.error('Failed to fetch warehouses:', error)
  }
}

const fetchLogs = async () => {
  if (!selectedWarehouseId.value) {
    logs.value = []
    return
  }

  isLoading.value = true
  try {
    const result = await warehouseService.getInventoryLogs(selectedWarehouseId.value, {
      page: currentPage.value,
      per_page: perPage.value
    })
    
    logs.value = result.data || []
    currentPage.value = result.current_page || 1
    totalPages.value = result.last_page || 1
    total.value = result.total || 0
  } catch (error: any) {
    console.error('Failed to fetch logs:', error)
    await swal.error('Lấy lịch sử biến động thất bại!')
    logs.value = []
  } finally {
    isLoading.value = false
  }
}

const changePage = (page: number) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
    fetchLogs()
  }
}

onMounted(async () => {
  await fetchWarehouses()
  if (selectedWarehouseId.value) {
    await fetchLogs()
  }
})
</script>

<template>
  <div class="h-full flex flex-col p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6 flex-shrink-0">
      <div>
        <h1 class="text-2xl font-bold text-white">Lịch sử biến động kho</h1>
        <p class="text-slate-400 mt-1">BR-09.2: Mọi biến động tồn kho đều có log (Không được xóa)</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-dark-800 rounded-xl border border-white/10 p-4 mb-6 flex-shrink-0">
      <div class="flex gap-4 items-center">
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Chọn kho</label>
          <select v-model.number="selectedWarehouseId" @change="fetchLogs" class="form-input w-64">
            <option :value="null">-- Chọn kho --</option>
            <option v-for="wh in warehouses" :key="wh.id" :value="wh.id">{{ wh.name }}</option>
          </select>
        </div>
        <div class="flex-1"></div>
        <div class="text-sm text-slate-400">
          Tổng: <span class="text-white font-semibold">{{ total }}</span> bản ghi
        </div>
      </div>
    </div>

    <!-- Table Container -->
    <div class="flex-1 min-h-0 bg-dark-800 rounded-xl border border-white/10 overflow-hidden flex flex-col">
      <div v-if="isLoading" class="flex-1 flex items-center justify-center">
        <div class="inline-block w-8 h-8 border-4 border-primary/20 border-t-primary rounded-full animate-spin"></div>
      </div>

      <div v-else-if="!selectedWarehouseId" class="flex-1 flex items-center justify-center">
        <p class="text-slate-400">Vui lòng chọn kho để xem lịch sử</p>
      </div>

      <div v-else class="flex-1 overflow-auto">
        <table class="w-full">
          <thead class="sticky top-0 z-10 bg-dark-700">
            <tr class="border-b border-white/10">
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Thời gian</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Loại</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Sản phẩm</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Trước</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Sau</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Thay đổi</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Người thao tác</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Lý do/Ghi chú</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5">
            <tr v-for="log in logs" :key="log.id" class="hover:bg-white/5 transition-colors">
              <td class="px-6 py-4">
                <p class="text-sm text-slate-300">{{ new Date(log.created_at).toLocaleString('vi-VN') }}</p>
              </td>
              <td class="px-6 py-4">
                <span :class="[
                  'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium',
                  movementTypeConfig[log.movement_type]?.class || 'bg-slate-500/10 text-slate-400'
                ]">
                  {{ movementTypeConfig[log.movement_type]?.text || log.movement_type }}
                </span>
              </td>
              <td class="px-6 py-4">
                <p class="text-white">{{ log.product?.name || 'N/A' }}</p>
                <p v-if="log.product_variant_id" class="text-xs text-slate-400">Variant ID: {{ log.product_variant_id }}</p>
              </td>
              <td class="px-6 py-4">
                <span class="text-slate-300">{{ log.quantity_before || 0 }}</span>
              </td>
              <td class="px-6 py-4">
                <span class="text-white font-semibold">{{ log.quantity_after || 0 }}</span>
              </td>
              <td class="px-6 py-4">
                <span :class="[
                  'font-semibold',
                  log.movement_type === 'outbound' || log.movement_type === 'qc_fail' 
                    ? 'text-error' 
                    : log.movement_type === 'inbound' || log.movement_type === 'qc_pass'
                    ? 'text-success'
                    : 'text-info'
                ]">
                  {{ log.movement_type === 'outbound' || log.movement_type === 'qc_fail' ? '-' : '+' }}{{ log.quantity || 0 }}
                </span>
              </td>
              <td class="px-6 py-4">
                <p class="text-sm text-slate-300">{{ log.user?.name || 'System' }}</p>
              </td>
              <td class="px-6 py-4">
                <div class="max-w-xs">
                  <p v-if="log.reason" class="text-sm text-info font-medium mb-1">{{ log.reason }}</p>
                  <p v-if="log.note" class="text-xs text-slate-400">{{ log.note }}</p>
                  <p v-if="log.inboundBatch" class="text-xs text-slate-500 mt-1">
                    Batch: {{ log.inboundBatch.batch_number }}
                  </p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-if="!logs.length && selectedWarehouseId" class="py-16 text-center">
          <p class="text-slate-400">Chưa có lịch sử biến động nào</p>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1 && selectedWarehouseId" class="flex items-center justify-center gap-2 p-4 border-t border-white/10">
      <button @click="changePage(currentPage - 1)" :disabled="currentPage <= 1"
        class="btn btn-secondary btn-sm" :class="{ 'opacity-50 cursor-not-allowed': currentPage <= 1 }">
        Trước
      </button>
      <span class="text-slate-400 text-sm">{{ currentPage }} / {{ totalPages }}</span>
      <button @click="changePage(currentPage + 1)" :disabled="currentPage >= totalPages"
        class="btn btn-secondary btn-sm"
        :class="{ 'opacity-50 cursor-not-allowed': currentPage >= totalPages }">
        Sau
      </button>
    </div>
  </div>
</template>

