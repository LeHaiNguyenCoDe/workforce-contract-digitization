<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { reviewColumns } from '../../configs/columns'
import { useReviewStore } from '../../store/store'
import { useReviews } from '../../composables/useReviews'

const { t } = useI18n()

// Store
const store = useReviewStore()

// Composables
const {
  filteredReviews,
  formatDate,
  getStatusColor,
  getStatusText,
  setStatusFilter,
  approveReview,
  rejectReview,
  deleteReview
} = useReviews()

// Computed from store
const reviews = computed(() => store.reviews)
const isLoading = computed(() => store.isLoading)
const statusFilter = computed(() => store.statusFilter)

// Lifecycle
onMounted(async () => {
  await store.fetchReviews()
})

function handleStatusChange(event: Event) {
  const target = event.target as HTMLSelectElement
  setStatusFilter(target.value)
}
</script>

<template>
  <div class="h-full flex flex-col p-6">
    <!-- Header -->
    <AdminPageHeader :title="t('admin.reviews')" description="Kiểm duyệt và phản hồi đánh giá sản phẩm từ khách hàng" />

    <!-- Filters -->
    <AdminSearch :modelValue="''" @search="store.fetchReviews({ status: statusFilter || undefined })" placeholder="Tìm kiếm đánh giá...">
      <template #filters>
        <div class="flex gap-2">
          <select :value="statusFilter" @change="handleStatusChange" class="form-input w-48 bg-dark-700 border-white/10 text-white">
            <option value="">Tất cả trạng thái</option>
            <option value="pending">Chờ duyệt</option>
            <option value="approved">Đã duyệt</option>
            <option value="rejected">Từ chối</option>
          </select>
          <DButton variant="secondary" @click="store.fetchReviews({ status: statusFilter || undefined })" title="Làm mới">
            <img src="@/assets/admin/icons/refresh-cw.svg" class="w-4 h-4 brightness-0 invert opacity-70" alt="Refresh" />
          </DButton>
        </div>
      </template>
    </AdminSearch>

    <!-- Table -->
    <AdminTable :columns="reviewColumns" :data="filteredReviews" :loading="isLoading" empty-text="Chưa có đánh giá nào">
      <template #cell-user="{ item }">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-xs">
            {{ item.user?.name?.charAt(0)?.toUpperCase() || '?' }}
          </div>
          <div class="min-w-0">
            <p class="font-medium text-white truncate">{{ item.user?.name || 'Anonymous' }}</p>
            <p class="text-[10px] text-slate-500 truncate">{{ item.user?.email }}</p>
          </div>
        </div>
      </template>

      <template #cell-product="{ value }">
        <span class="text-sm text-primary-light font-medium">{{ value?.name || 'N/A' }}</span>
      </template>

      <template #cell-rating="{ value }">
        <div class="flex items-center gap-0.5">
          <svg v-for="i in 5" :key="i" xmlns="http://www.w3.org/2000/svg" width="12" height="12"
            viewBox="0 0 24 24" :fill="i <= value ? '#fbbf24' : 'none'"
            :class="i <= value ? 'text-yellow-400' : 'text-slate-600'" stroke="currentColor" stroke-width="2">
            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
          </svg>
          <span class="text-[10px] text-slate-400 ml-1">({{ value }})</span>
        </div>
      </template>

      <template #cell-content="{ value }">
        <p class="text-xs text-slate-400 max-w-xs truncate" :title="value">{{ value }}</p>
      </template>

      <template #cell-status="{ value }">
        <StatusBadge :status="value" :text="getStatusText(value)" />
      </template>

      <template #cell-created_at="{ value }">
        <span class="text-slate-500 text-xs">{{ formatDate(value) }}</span>
      </template>

      <template #actions="{ item }">
        <div class="flex items-center justify-end gap-1">
          <template v-if="item.status === 'pending'">
            <DAction icon="approve" title="Duyệt" variant="success" @click.stop="approveReview(item)" />
            <DAction icon="reject" title="Từ chối" variant="danger" @click.stop="rejectReview(item)" />
          </template>
          <DAction v-else icon="delete" variant="danger" @click.stop="deleteReview(item.id)" />
        </div>
      </template>
    </AdminTable>
  </div>
</template>
