<script setup lang="ts">
import { ref } from 'vue'
import { actionLabels } from '../../models/auditLog'
import { auditLogColumns } from '../../configs/columns'
import { useAuditLogs } from '../../composables/useAuditLogs'
import { formatDateTime } from '@/utils'

const { logs, isLoading, currentPage, totalPages, actionFilter, setActionFilter, changePage } = useAuditLogs()

const selectedLog = ref<any>(null)
const showDetailModal = ref(false)

function viewDetail(log: any) {
  selectedLog.value = log
  showDetailModal.value = true
}

function handleActionChange(event: Event) {
  const target = event.target as HTMLSelectElement
  setActionFilter(target.value)
}
</script>

<template>
  <div class="h-full flex flex-col p-6">
    <!-- Header -->
    <AdminPageHeader title="Nhật ký Hệ thống" description="Theo dõi và truy xuất toàn bộ lịch sử hoạt động, thay đổi dữ liệu của người dùng" />

    <!-- Filters -->
    <AdminSearch :modelValue="''" placeholder="Tìm kiếm nhật ký..." class="mb-6">
      <template #filters>
        <div class="flex gap-2">
          <select :value="actionFilter" @change="handleActionChange" class="form-input w-48 bg-dark-700 border-white/10 text-white">
            <option value="">Tất cả hành động</option>
            <option v-for="[k, v] in Object.entries(actionLabels)" :key="k" :value="k">{{ v }}</option>
          </select>
        </div>
      </template>
    </AdminSearch>

    <!-- Table -->
    <AdminTable :columns="auditLogColumns" :data="logs" :loading="isLoading" empty-text="Hệ thống chưa ghi nhận bất kỳ hoạt động nào." @row-click="viewDetail">
      <template #cell-created_at="{ value }">
        <span class="text-slate-400 text-xs font-mono">{{ formatDateTime(value) }}</span>
      </template>

      <template #cell-user_name="{ value }">
        <div class="flex items-center gap-2">
          <div class="w-6 h-6 rounded-full bg-dark-600 flex items-center justify-center text-[10px] text-slate-400 border border-white/5">
            {{ value?.charAt(0)?.toUpperCase() || '?' }}
          </div>
          <span class="text-sm text-white font-medium">{{ value || 'Hệ thống' }}</span>
        </div>
      </template>

      <template #cell-action="{ value }">
        <StatusBadge :status="value" :text="actionLabels[value] || value" />
      </template>

      <template #cell-entity="{ item }">
        <div class="flex items-center gap-1.5">
          <span class="text-[10px] font-bold text-slate-500 uppercase tracking-tighter">{{ item.entity_type }}</span>
          <span class="text-xs text-primary-light font-mono">#{{ item.entity_id }}</span>
        </div>
      </template>

      <template #cell-ip_address="{ value }">
        <span class="text-slate-500 text-[10px] font-mono">{{ value || '-' }}</span>
      </template>

      <template #actions="{ item }">
        <DAction icon="view" title="Chi tiết thay đổi" @click.stop="viewDetail(item)" />
      </template>

      <template #footer>
        <Pagination :currentPage="currentPage" :totalPages="totalPages" @page-change="changePage" />
      </template>
    </AdminTable>

    <!-- Detail Modal -->
    <DModal v-model="showDetailModal" title="Chi tiết hoạt động hệ thống" size="lg">
      <div v-if="selectedLog" class="space-y-6">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 p-4 bg-dark-700/50 rounded-xl border border-white/5">
          <div>
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Người thực hiện</p>
            <p class="text-white font-bold">{{ selectedLog.user_name || 'Hệ thống' }}</p>
          </div>
          <div>
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Thời gian</p>
            <p class="text-white font-medium">{{ formatDateTime(selectedLog.created_at) }}</p>
          </div>
          <div>
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Hành động</p>
            <StatusBadge :status="selectedLog.action" :text="actionLabels[selectedLog.action]" />
          </div>
          <div>
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Địa chỉ IP</p>
            <p class="text-slate-400 font-mono text-xs">{{ selectedLog.ip_address || 'N/A' }}</p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div v-if="selectedLog.old_values" class="space-y-2">
            <h4 class="text-sm font-bold text-error flex items-center gap-2">
              <img src="@/assets/admin/icons/trash.svg" class="w-3.5 h-3.5" alt="Old Data" />
              Dữ liệu trước thay đổi
            </h4>
            <div class="bg-dark-900 ring-1 ring-white/5 p-4 rounded-xl overflow-auto max-h-[300px]">
              <pre class="text-[11px] text-slate-400 leading-relaxed">{{ JSON.stringify(selectedLog.old_values, null, 2) }}</pre>
            </div>
          </div>
          
          <div v-if="selectedLog.new_values" class="space-y-2">
            <h4 class="text-sm font-bold text-success flex items-center gap-2">
              <img src="@/assets/admin/icons/plus.svg" class="w-3.5 h-3.5" alt="New Data" />
              Dữ liệu sau thay đổi
            </h4>
            <div class="bg-dark-900 ring-1 ring-white/5 p-4 rounded-xl overflow-auto max-h-[300px]">
              <pre class="text-[11px] text-success/80 leading-relaxed">{{ JSON.stringify(selectedLog.new_values, null, 2) }}</pre>
            </div>
          </div>
        </div>

        <div v-if="selectedLog.user_agent" class="p-3 bg-dark-900 rounded-lg border border-white/5">
          <p class="text-[10px] font-bold text-slate-600 uppercase mb-1">Trình duyệt / Thiết bị (User Agent)</p>
          <p class="text-[11px] text-slate-500 italic">{{ selectedLog.user_agent }}</p>
        </div>
      </div>
      <template #footer>
        <DButton variant="secondary" class="w-full" @click="showDetailModal = false">Đóng cửa sổ</DButton>
      </template>
    </DModal>
  </div>
</template>
