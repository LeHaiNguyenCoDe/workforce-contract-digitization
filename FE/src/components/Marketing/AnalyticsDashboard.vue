<template>
  <div class="analytics-dashboard">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Bảng Điều Khiển Marketing</h1>
        <p class="text-gray-600 mt-1">Phân tích toàn bộ hoạt động marketing</p>
      </div>
      <div class="flex gap-2">
        <select v-model="dateRange" class="select select-bordered">
          <option value="week">Tuần này</option>
          <option value="month">Tháng này</option>
          <option value="quarter">Quý này</option>
          <option value="year">Năm này</option>
        </select>
        <button @click="refreshData" class="btn btn-outline">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
          </svg>
          Làm Mới
        </button>
      </div>
    </div>

    <!-- KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="card bg-gradient-to-br from-blue-500 to-blue-600 text-white">
        <div class="card-body">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-blue-100 text-sm">Leads Tạo Mới</p>
              <p class="text-3xl font-bold">{{ dashboard.leads?.created || 0 }}</p>
              <p class="text-blue-100 text-xs mt-1">↑ 12% so với trước</p>
            </div>
            <svg class="w-10 h-10 text-blue-200" fill="currentColor" viewBox="0 0 20 20">
              <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"></path>
            </svg>
          </div>
        </div>
      </div>

      <div class="card bg-gradient-to-br from-green-500 to-green-600 text-white">
        <div class="card-body">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-green-100 text-sm">Chuyển Đổi</p>
              <p class="text-3xl font-bold">{{ dashboard.leads?.converted || 0 }}</p>
              <p class="text-green-100 text-xs mt-1">Tỷ lệ: {{ dashboard.leads?.conversion_rate || 0 }}%</p>
            </div>
            <svg class="w-10 h-10 text-green-200" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
          </div>
        </div>
      </div>

      <div class="card bg-gradient-to-br from-purple-500 to-purple-600 text-white">
        <div class="card-body">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-purple-100 text-sm">Mã Sử Dụng</p>
              <p class="text-3xl font-bold">{{ dashboard.coupons?.used || 0 }}</p>
              <p class="text-purple-100 text-xs mt-1">Giảm: {{ formatCurrency(dashboard.coupons?.total_discount || 0) }}</p>
            </div>
            <svg class="w-10 h-10 text-purple-200" fill="currentColor" viewBox="0 0 20 20">
              <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"></path>
            </svg>
          </div>
        </div>
      </div>

      <div class="card bg-gradient-to-br from-orange-500 to-orange-600 text-white">
        <div class="card-body">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-orange-100 text-sm">Revenue</p>
              <p class="text-3xl font-bold">{{ formatCurrency(dashboard.revenue?.total || 0) }}</p>
              <p class="text-orange-100 text-xs mt-1">AOV: {{ formatCurrency(dashboard.revenue?.average_order_value || 0) }}</p>
            </div>
            <svg class="w-10 h-10 text-orange-200" fill="currentColor" viewBox="0 0 20 20">
              <path d="M8.16 2.75a.75.75 0 00-.75.75v1.5h-2a.75.75 0 000 1.5h.75v6h-.75a.75.75 0 000 1.5h2v1.5a.75.75 0 001.5 0v-1.5h2v1.5a.75.75 0 001.5 0v-1.5h2a.75.75 0 000-1.5h-.75v-6h.75a.75.75 0 000-1.5h-2v-1.5a.75.75 0 00-1.5 0v1.5h-2v-1.5a.75.75 0 00-.75-.75z"></path>
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <!-- Conversion Funnel -->
      <div class="card bg-white">
        <div class="card-body">
          <h3 class="font-bold text-lg mb-4">Conversion Funnel</h3>
          <div class="space-y-3">
            <div>
              <div class="flex justify-between text-sm mb-1">
                <span class="font-medium">Total Leads</span>
                <span>{{ funnel.total_leads }}</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-blue-600 h-2 rounded-full" style="width: 100%"></div>
              </div>
            </div>
            <div>
              <div class="flex justify-between text-sm mb-1">
                <span class="font-medium">Contacted</span>
                <span>{{ funnel.contacted?.count }} ({{ funnel.contacted?.rate }}%)</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-green-600 h-2 rounded-full" :style="{ width: funnel.contacted?.rate + '%' }"></div>
              </div>
            </div>
            <div>
              <div class="flex justify-between text-sm mb-1">
                <span class="font-medium">Qualified</span>
                <span>{{ funnel.qualified?.count }} ({{ funnel.qualified?.rate }}%)</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-orange-600 h-2 rounded-full" :style="{ width: funnel.qualified?.rate + '%' }"></div>
              </div>
            </div>
            <div>
              <div class="flex justify-between text-sm mb-1">
                <span class="font-medium">Converted</span>
                <span>{{ funnel.converted?.count }} ({{ funnel.converted?.rate }}%)</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-purple-600 h-2 rounded-full" :style="{ width: funnel.converted?.rate + '%' }"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Top Segments -->
      <div class="card bg-white">
        <div class="card-body">
          <h3 class="font-bold text-lg mb-4">Segments Hàng Đầu</h3>
          <div class="space-y-3">
            <div v-for="(segment, idx) in topSegments" :key="idx" class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div :style="{ backgroundColor: segment.color || '#3B82F6' }" class="w-4 h-4 rounded-full"></div>
                <div>
                  <p class="font-medium text-sm">{{ segment.name }}</p>
                  <p class="text-xs text-gray-600">{{ segment.customer_count }} khách hàng</p>
                </div>
              </div>
              <div class="text-right">
                <p class="font-semibold text-sm">{{ ((segment.customer_count / dashboard.segmentation?.total_customers) * 100).toFixed(1) }}%</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- LTV Analysis -->
    <div class="card bg-white mb-6">
      <div class="card-body">
        <h3 class="font-bold text-lg mb-4">Customer Lifetime Value</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="border border-gray-200 rounded-lg p-4 text-center">
            <p class="text-gray-600 text-sm mb-1">Average LTV</p>
            <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(ltv?.average_ltv || 0) }}</p>
          </div>
          <div class="border border-gray-200 rounded-lg p-4 text-center">
            <p class="text-gray-600 text-sm mb-1">Total LTV</p>
            <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(ltv?.total_ltv || 0) }}</p>
          </div>
          <div class="border border-gray-200 rounded-lg p-4 text-center">
            <p class="text-gray-600 text-sm mb-1">VIP (>2x Avg)</p>
            <p class="text-2xl font-bold text-purple-600">{{ ltv?.segmentation?.vip || 0 }}</p>
          </div>
          <div class="border border-gray-200 rounded-lg p-4 text-center">
            <p class="text-gray-600 text-sm mb-1">Max LTV</p>
            <p class="text-2xl font-bold text-green-600">{{ formatCurrency(ltv?.max_ltv || 0) }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- ROI Analysis -->
    <div class="card bg-white">
      <div class="card-body">
        <h3 class="font-bold text-lg mb-4">Marketing ROI</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="space-y-2">
            <p class="text-gray-600 text-sm">Revenue</p>
            <p class="text-3xl font-bold text-gray-900">{{ formatCurrency(roi?.revenue || 0) }}</p>
          </div>
          <div class="space-y-2">
            <p class="text-gray-600 text-sm">Marketing Cost (Est.)</p>
            <p class="text-3xl font-bold text-gray-900">{{ formatCurrency(roi?.estimated_marketing_cost || 0) }}</p>
          </div>
          <div class="space-y-2">
            <p class="text-gray-600 text-sm">ROI</p>
            <p :class="(roi?.roi_percentage || 0) >= 0 ? 'text-green-600' : 'text-red-600'" class="text-3xl font-bold">
              {{ (roi?.roi_percentage || 0) >= 0 ? '+' : '' }}{{ roi?.roi_percentage || 0 }}%
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'

const dateRange = ref('month')

const dashboard = ref({
  leads: { created: 0, converted: 0, conversion_rate: 0 },
  coupons: { used: 0, total_discount: 0 },
  revenue: { total: 0, average_order_value: 0 },
  segmentation: { total_customers: 0 }
})

const funnel = ref({
  total_leads: 0,
  contacted: { count: 0, rate: 0 },
  qualified: { count: 0, rate: 0 },
  converted: { count: 0, rate: 0 }
})

const ltv = ref({
  average_ltv: 0,
  total_ltv: 0,
  max_ltv: 0,
  segmentation: { vip: 0 }
})

const roi = ref({
  revenue: 0,
  estimated_marketing_cost: 0,
  roi_percentage: 0
})

interface Segment {
  name: string
  customer_count: number
  color?: string
}

const topSegments = ref<Segment[]>([])

const fetchDashboard = async () => {
  try {
    const response = await fetch('/api/v1/marketing/analytics/dashboard')
    const data = await response.json()
    if (data?.data) {
      dashboard.value = data.data
    }
  } catch (error) {
    console.error('Error fetching dashboard:', error)
  }
}

const fetchFunnel = async () => {
  try {
    const response = await fetch('/api/v1/marketing/analytics/funnel')
    const data = await response.json()
    if (data?.data) {
      funnel.value = data.data
    }
  } catch (error) {
    console.error('Error fetching funnel:', error)
  }
}

const fetchLTV = async () => {
  try {
    const response = await fetch('/api/v1/marketing/analytics/ltv')
    const data = await response.json()
    if (data?.data) {
      ltv.value = data.data
    }
  } catch (error) {
    console.error('Error fetching LTV:', error)
  }
}

const fetchROI = async () => {
  try {
    const response = await fetch('/api/v1/marketing/analytics/roi')
    const data = await response.json()
    if (data?.data) {
      roi.value = data.data
    }
  } catch (error) {
    console.error('Error fetching ROI:', error)
  }
}

const fetchSegments = async () => {
  try {
    const response = await fetch('/api/v1/marketing/analytics/segments')
    const data = await response.json()
    if (data?.data?.segments) {
      topSegments.value = data.data.segments.slice(0, 4)
    } else {
      topSegments.value = []
    }
  } catch (error) {
    console.error('Error fetching segments:', error)
    topSegments.value = []
  }
}

const refreshData = () => {
  fetchDashboard()
  fetchFunnel()
  fetchLTV()
  fetchROI()
  fetchSegments()
}

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND',
    maximumFractionDigits: 0
  }).format(value)
}

onMounted(() => {
  refreshData()
})
</script>

<style scoped>
.analytics-dashboard {
  @apply p-6;
}

.card {
  @apply rounded-lg shadow bg-white;
}

.card-body {
  @apply p-6;
}

.btn {
  @apply px-4 py-2 rounded-lg font-medium transition-colors;
}

.btn-outline {
  @apply border border-gray-300 text-gray-700 hover:bg-gray-50;
}

.select {
  @apply px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500;
}
</style>
