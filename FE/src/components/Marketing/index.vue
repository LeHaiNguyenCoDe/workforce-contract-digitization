<template>
  <div class="marketing-module">
    <!-- Navigation Tabs -->
    <div class="flex gap-0 border-b mb-6 sticky top-0 bg-white z-10">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        @click="activeTab = tab.id"
        :class="[
          'px-6 py-3 font-medium border-b-2 transition-colors',
          activeTab === tab.id
            ? 'border-blue-600 text-blue-600'
            : 'border-transparent text-gray-600 hover:text-gray-900'
        ]"
      >
        <div class="flex items-center gap-2">
          <svg v-if="tab.icon" class="w-4 h-4" v-html="tab.icon"></svg>
          {{ tab.label }}
        </div>
      </button>
    </div>

    <!-- Tab Content -->
    <div>
      <!-- Dashboard Tab -->
      <div v-if="activeTab === 'dashboard'" class="animate-fadeIn">
        <AnalyticsDashboard />
      </div>

      <!-- Leads Tab -->
      <div v-if="activeTab === 'leads'" class="animate-fadeIn">
        <LeadsManagement />
      </div>

      <!-- Coupons Tab -->
      <div v-if="activeTab === 'coupons'" class="animate-fadeIn">
        <CouponsManagement />
      </div>

      <!-- Segmentation Tab -->
      <div v-if="activeTab === 'segmentation'" class="animate-fadeIn">
        <SegmentationManagement />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import AnalyticsDashboard from './AnalyticsDashboard.vue'
import LeadsManagement from './LeadsManagement.vue'
import CouponsManagement from './CouponsManagement.vue'
import SegmentationManagement from './SegmentationManagement.vue'

const activeTab = ref('dashboard')

const tabs = [
  {
    id: 'dashboard',
    label: 'Bảng Điều Khiển',
    icon: '<circle cx="12" cy="12" r="1"></circle><circle cx="19" cy="12" r="1"></circle><circle cx="5" cy="12" r="1"></circle>'
  },
  {
    id: 'leads',
    label: 'Leads',
    icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 6a3 3 0 11-6 0 3 3 0 016 0zM6 20a6 6 0 016-6v0"></path>'
  },
  {
    id: 'coupons',
    label: 'Mã Giảm Giá',
    icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>'
  },
  {
    id: 'segmentation',
    label: 'Phân Khúc',
    icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>'
  }
]
</script>

<style scoped>
.marketing-module {
  @apply min-h-screen bg-gray-50;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fadeIn {
  animation: fadeIn 0.3s ease-in-out;
}
</style>
