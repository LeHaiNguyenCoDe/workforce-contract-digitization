<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import httpClient from '@/plugins/api/httpClient'
import { warehouseService } from '@/plugins/api/services/WarehouseService'
import BaseModal from '@/components/BaseModal.vue'
import { useSwal } from '@/utils'
import type { InboundBatch, InboundBatchItem, CreateInboundBatchRequest, ReceiveInboundBatchRequest } from '@/plugins/api/services/WarehouseService'

const swal = useSwal()

const batches = ref<InboundBatch[]>([])
const isLoading = ref(true)
const statusFilter = ref('')
const warehouseFilter = ref<number | null>(null)
const showCreateModal = ref(false)
const showReceiveModal = ref(false)
const selectedBatch = ref<InboundBatch | null>(null)
const isSaving = ref(false)

const warehouses = ref<any[]>([])
const suppliers = ref<any[]>([])
const products = ref<any[]>([])

const createForm = ref<CreateInboundBatchRequest>({
  warehouse_id: 0,
  supplier_id: 0,
  items: [{ product_id: 0, quantity_received: 0 }],
  notes: ''
})

const receiveForm = ref<ReceiveInboundBatchRequest>({
  received_date: new Date().toISOString().split('T')[0],
  items: [],
  notes: ''
})

const statusConfig: Record<string, { text: string; class: string }> = {
  pending: { text: 'Chờ nhận', class: 'bg-slate-500/10 text-slate-400' },
  received: { text: 'Đã nhận', class: 'bg-info/10 text-info' },
  qc_in_progress: { text: 'Đang QC', class: 'bg-warning/10 text-warning' },
  qc_completed: { text: 'QC xong', class: 'bg-success/10 text-success' },
  completed: { text: 'Hoàn tất', class: 'bg-success/10 text-success' },
  cancelled: { text: 'Hủy', class: 'bg-error/10 text-error' }
}

const filteredBatches = computed(() => {
  let result = batches.value
  if (statusFilter.value) {
    result = result.filter(b => b.status === statusFilter.value)
  }
  if (warehouseFilter.value) {
    result = result.filter(b => b.warehouse_id === warehouseFilter.value)
  }
  return result
})

const canEditBatch = (batch: InboundBatch) => {
  return ['pending', 'received'].includes(batch.status)
}

const canReceiveBatch = (batch: InboundBatch) => {
  return batch.status === 'pending'
}

const canQC = (batch: InboundBatch) => {
  return batch.status === 'received'
}

const fetchBatches = async () => {
  isLoading.value = true
  try {
    const filters: any = {}
    if (statusFilter.value) filters.status = statusFilter.value
    if (warehouseFilter.value) filters.warehouse_id = warehouseFilter.value
    
    batches.value = await warehouseService.getInboundBatches(filters)
  } catch (error: any) {
    console.error('Failed to fetch batches:', error)
    await swal.error(error.response?.data?.message || 'Lấy danh sách lô nhập thất bại!')
    batches.value = []
  } finally {
    isLoading.value = false
  }
}

const fetchMetadata = async () => {
  try {
    const [whRes, supRes, prodRes] = await Promise.all([
      httpClient.get('admin/warehouses'),
      httpClient.get('admin/suppliers'),
      httpClient.get('admin/products', { params: { per_page: 1000 } })
    ])
    
    warehouses.value = (whRes.data as any).data?.data || (whRes.data as any).data || []
    suppliers.value = (supRes.data as any).data?.data || (supRes.data as any).data || []
    products.value = (prodRes.data as any).data?.data || (prodRes.data as any).data || []
    
    // Set default warehouse
    if (warehouses.value.length > 0 && !createForm.value.warehouse_id) {
      createForm.value.warehouse_id = warehouses.value[0].id
    }
  } catch (error) {
    console.error('Failed to fetch metadata:', error)
  }
}

const openCreateModal = () => {
  selectedBatch.value = null
  createForm.value = {
    warehouse_id: warehouses.value[0]?.id || 0,
    supplier_id: 0,
    items: [{ product_id: 0, quantity_received: 0 }],
    notes: ''
  }
  showCreateModal.value = true
}

const addItem = () => {
  createForm.value.items.push({ product_id: 0, quantity_received: 0 })
}

const removeItem = (index: number) => {
  createForm.value.items.splice(index, 1)
}

const createBatch = async () => {
  if (isSaving.value) return
  
  // Validate
  if (!createForm.value.warehouse_id || !createForm.value.supplier_id) {
    await swal.warning('Vui lòng chọn kho và nhà cung cấp!')
    return
  }
  
  if (createForm.value.items.length === 0 || createForm.value.items.some(item => !item.product_id || item.quantity_received <= 0)) {
    await swal.warning('Vui lòng thêm ít nhất 1 sản phẩm với số lượng > 0!')
    return
  }
  
  isSaving.value = true
  try {
    await warehouseService.createInboundBatch(createForm.value)
    showCreateModal.value = false
    await swal.success('Tạo lô nhập thành công!')
    await fetchBatches()
  } catch (error: any) {
    console.error('Failed to create batch:', error)
    await swal.error(error.response?.data?.message || 'Tạo lô nhập thất bại!')
  } finally {
    isSaving.value = false
  }
}

const openReceiveModal = async (batch: InboundBatch) => {
  selectedBatch.value = batch
  receiveForm.value = {
    received_date: new Date().toISOString().split('T')[0],
    items: batch.items?.map(item => ({
      product_id: item.product_id,
      product_variant_id: item.product_variant_id || null,
      quantity_received: item.quantity_received
    })) || [],
    notes: ''
  }
  showReceiveModal.value = true
}

const receiveBatch = async () => {
  if (isSaving.value || !selectedBatch.value) return
  
  isSaving.value = true
  try {
    await warehouseService.receiveInboundBatch(selectedBatch.value.id, receiveForm.value)
    showReceiveModal.value = false
    await swal.success('Nhận hàng thành công!')
    await fetchBatches()
  } catch (error: any) {
    console.error('Failed to receive batch:', error)
    await swal.error(error.response?.data?.message || 'Nhận hàng thất bại!')
  } finally {
    isSaving.value = false
  }
}

const viewBatch = async (batch: InboundBatch) => {
  try {
    const fullBatch = await warehouseService.getInboundBatch(batch.id)
    selectedBatch.value = fullBatch
    // Show batch details in a modal or navigate to detail page
    await swal.info(`Lô: ${fullBatch.batch_number}\nTrạng thái: ${statusConfig[fullBatch.status]?.text}\nSố sản phẩm: ${fullBatch.items?.length || 0}`)
  } catch (error: any) {
    await swal.error('Lấy thông tin lô nhập thất bại!')
  }
}

onMounted(() => {
  fetchMetadata()
  fetchBatches()
})
</script>

<template>
  <div class="h-full flex flex-col p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6 flex-shrink-0">
      <div>
        <h1 class="text-2xl font-bold text-white">Lô nhập kho</h1>
        <p class="text-slate-400 mt-1">Quản lý lô nhập từ nhà cung cấp (BR-02.1, BR-02.2)</p>
      </div>
      <button @click="openCreateModal" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M5 12h14" />
          <path d="M12 5v14" />
        </svg>
        Tạo lô nhập
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-dark-800 rounded-xl border border-white/10 p-4 mb-6 flex-shrink-0">
      <div class="flex gap-4">
        <select v-model="statusFilter" @change="fetchBatches" class="form-input w-48">
          <option value="">Tất cả trạng thái</option>
          <option value="pending">Chờ nhận</option>
          <option value="received">Đã nhận</option>
          <option value="qc_in_progress">Đang QC</option>
          <option value="qc_completed">QC xong</option>
          <option value="completed">Hoàn tất</option>
          <option value="cancelled">Hủy</option>
        </select>
        <select v-model="warehouseFilter" @change="fetchBatches" class="form-input w-48">
          <option :value="null">Tất cả kho</option>
          <option v-for="wh in warehouses" :key="wh.id" :value="wh.id">{{ wh.name }}</option>
        </select>
      </div>
    </div>

    <!-- Table Container -->
    <div class="flex-1 min-h-0 bg-dark-800 rounded-xl border border-white/10 overflow-hidden flex flex-col">
      <div v-if="isLoading" class="flex-1 flex items-center justify-center">
        <div class="inline-block w-8 h-8 border-4 border-primary/20 border-t-primary rounded-full animate-spin"></div>
      </div>

      <div v-else class="flex-1 overflow-auto">
        <table class="w-full">
          <thead class="sticky top-0 z-10 bg-dark-700">
            <tr class="border-b border-white/10">
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Số lô</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Kho</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Nhà cung cấp</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Ngày nhận</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Số sản phẩm</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Trạng thái</th>
              <th class="px-6 py-4 text-right text-sm font-semibold text-slate-400">Thao tác</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5">
            <tr v-for="batch in filteredBatches" :key="batch.id" class="hover:bg-white/5 transition-colors">
              <td class="px-6 py-4">
                <span class="font-mono text-primary">{{ batch.batch_number }}</span>
              </td>
              <td class="px-6 py-4">
                <p class="text-white">{{ batch.warehouse?.name || 'N/A' }}</p>
              </td>
              <td class="px-6 py-4">
                <p class="text-white">{{ batch.supplier?.name || 'N/A' }}</p>
              </td>
              <td class="px-6 py-4">
                <p class="text-slate-400">{{ batch.received_date || '-' }}</p>
              </td>
              <td class="px-6 py-4">
                <span class="text-slate-300">{{ batch.items?.length || 0 }}</span>
              </td>
              <td class="px-6 py-4">
                <span :class="[
                  'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium',
                  statusConfig[batch.status]?.class || 'bg-slate-500/10 text-slate-400'
                ]">
                  {{ statusConfig[batch.status]?.text || batch.status }}
                </span>
              </td>
              <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button @click="viewBatch(batch)"
                    class="w-8 h-8 rounded-lg bg-info/10 text-info hover:bg-info/20 transition-colors flex items-center justify-center"
                    title="Xem chi tiết">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                      <circle cx="12" cy="12" r="3" />
                    </svg>
                  </button>
                  <button v-if="canReceiveBatch(batch)" @click="openReceiveModal(batch)"
                    class="w-8 h-8 rounded-lg bg-success/10 text-success hover:bg-success/20 transition-colors flex items-center justify-center"
                    title="Nhận hàng">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M5 12h14" />
                      <path d="M12 5v14" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-if="!filteredBatches.length" class="py-16 text-center">
          <p class="text-slate-400">Chưa có lô nhập nào</p>
        </div>
      </div>
    </div>

    <!-- Create Batch Modal -->
    <BaseModal v-model="showCreateModal" title="Tạo lô nhập mới (BR-02.1)" size="lg">
      <div class="space-y-4">
        <div class="bg-info/10 border border-info/20 rounded-lg p-3 mb-4">
          <p class="text-sm text-info">
            <strong>Lưu ý:</strong> Mỗi lần nhập phải tạo Inbound Batch. Batch chỉ ghi nhận số lượng nhận, chưa cộng tồn khi RECEIVED.
          </p>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Kho hàng *</label>
            <select v-model.number="createForm.warehouse_id" class="form-input">
              <option :value="0">-- Chọn kho --</option>
              <option v-for="wh in warehouses" :key="wh.id" :value="wh.id">{{ wh.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Nhà cung cấp *</label>
            <select v-model.number="createForm.supplier_id" class="form-input">
              <option :value="0">-- Chọn nhà cung cấp --</option>
              <option v-for="sup in suppliers" :key="sup.id" :value="sup.id">{{ sup.name }}</option>
            </select>
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Sản phẩm trong lô *</label>
          <div v-for="(item, index) in createForm.items" :key="index" class="flex gap-2 mb-2">
            <select v-model.number="item.product_id" class="form-input flex-1">
              <option :value="0">-- Chọn sản phẩm --</option>
              <option v-for="prod in products" :key="prod.id" :value="prod.id">{{ prod.name }}</option>
            </select>
            <input v-model.number="item.quantity_received" type="number" min="1" placeholder="Số lượng" class="form-input w-32" />
            <button v-if="createForm.items.length > 1" @click="removeItem(index)"
              class="w-10 h-10 rounded-lg bg-error/10 text-error hover:bg-error/20 flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 6h18" />
              </svg>
            </button>
          </div>
          <button @click="addItem" class="btn btn-secondary btn-sm mt-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M5 12h14" />
              <path d="M12 5v14" />
            </svg>
            Thêm sản phẩm
          </button>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Ghi chú</label>
          <textarea v-model="createForm.notes" rows="2" class="form-input" placeholder="Ghi chú (tùy chọn)"></textarea>
        </div>
      </div>
      <template #footer>
        <div class="flex justify-end gap-3">
          <button @click="showCreateModal = false" class="btn btn-secondary" :disabled="isSaving">Hủy</button>
          <button @click="createBatch" class="btn btn-primary" :disabled="isSaving">
            <span v-if="isSaving" class="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin mr-2"></span>
            {{ isSaving ? 'Đang tạo...' : 'Tạo lô nhập' }}
          </button>
        </div>
      </template>
    </BaseModal>

    <!-- Receive Batch Modal -->
    <BaseModal v-model="showReceiveModal" title="Nhận hàng (BR-02.2)" size="lg">
      <div v-if="selectedBatch" class="space-y-4">
        <div class="bg-info/10 border border-info/20 rounded-lg p-3 mb-4">
          <p class="text-sm text-info">
            <strong>Lưu ý:</strong> Nhận hàng chỉ ghi nhận số lượng, chưa cộng tồn kho. Tồn kho chỉ được tạo sau khi QC PASS.
          </p>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Số lô</label>
          <input :value="selectedBatch.batch_number" type="text" disabled class="form-input bg-dark-700 text-slate-400 cursor-not-allowed" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Ngày nhận</label>
          <input v-model="receiveForm.received_date" type="date" class="form-input" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Sản phẩm nhận</label>
          <div v-for="(item, index) in receiveForm.items" :key="index" class="mb-2 p-3 bg-dark-700 rounded-lg">
            <p class="text-white font-medium mb-1">{{ products.find(p => p.id === item.product_id)?.name || 'N/A' }}</p>
            <input v-model.number="item.quantity_received" type="number" min="0" placeholder="Số lượng nhận" class="form-input" />
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Ghi chú</label>
          <textarea v-model="receiveForm.notes" rows="2" class="form-input" placeholder="Ghi chú (tùy chọn)"></textarea>
        </div>
      </div>
      <template #footer>
        <div class="flex justify-end gap-3">
          <button @click="showReceiveModal = false" class="btn btn-secondary" :disabled="isSaving">Hủy</button>
          <button @click="receiveBatch" class="btn btn-primary" :disabled="isSaving">
            <span v-if="isSaving" class="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin mr-2"></span>
            {{ isSaving ? 'Đang xử lý...' : 'Xác nhận nhận hàng' }}
          </button>
        </div>
      </template>
    </BaseModal>
  </div>
</template>

