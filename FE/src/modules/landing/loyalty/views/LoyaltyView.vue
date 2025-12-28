<script setup lang="ts">
/**
 * Loyalty View
 * Uses useLoyalty composable for logic separation
 */
import { ref, onMounted, computed } from 'vue'
import { useLoyalty } from '../composables/useLoyalty'
import { loyaltyTiers } from '../configs'

// Use composable
const {
    points,
    tier,
    history,
    isLoading,
    formatPoints,
    getTierLabel,
    loadLoyaltyInfo,
    loadHistory
} = useLoyalty()

// Tier color mapping
const tierColors: Record<string, string> = {
    bronze: '#CD7F32',
    silver: '#C0C0C0',
    gold: '#FFD700',
    platinum: '#E5E4E2',
    diamond: '#B9F2FF'
}

const tierColor = computed(() => tier.value ? tierColors[tier.value.id] || '#CD7F32' : '#CD7F32')

// Format date helper
const formatDate = (date: string) => new Date(date).toLocaleDateString('vi-VN')

// Fetch data on mount
onMounted(async () => {
    await loadLoyaltyInfo()
    await loadHistory()
})
</script>

<template>
    <div class="container py-8">
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-8">Điểm thưởng</h1>

        <!-- Loading -->
        <div v-if="isLoading" class="h-96 bg-dark-700 rounded-2xl animate-pulse"></div>

        <!-- Loyalty Content -->
        <div v-else-if="points !== null" class="space-y-6">
            <!-- Points Summary Card -->
            <div class="card bg-gradient-primary border-0 p-8">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="text-center md:text-left">
                        <p class="text-white/80 mb-2">Điểm hiện có</p>
                        <div class="text-5xl md:text-6xl font-bold text-white">
                            {{ formatPoints(points) }}
                        </div>
                        <p class="text-white/60 mt-2">điểm</p>
                    </div>
                    
                    <div v-if="tier" class="px-6 py-3 rounded-full font-bold text-dark-900" :style="{ background: tierColor }">
                        {{ getTierLabel(tier.id) }}
                    </div>
                </div>
            </div>

            <!-- Tier Progress -->
            <div class="card">
                <h3 class="text-xl font-bold text-white mb-6">Cấp bậc thành viên</h3>
                <div class="flex items-center gap-2">
                    <div v-for="t in loyaltyTiers" :key="t.id" 
                        class="flex-1 h-3 rounded-full transition-all"
                        :class="tier && loyaltyTiers.findIndex(x => x.id === tier.id) >= loyaltyTiers.findIndex(x => x.id === t.id) 
                            ? 'bg-primary' : 'bg-dark-600'">
                    </div>
                </div>
                <div class="flex justify-between mt-2">
                    <span v-for="t in loyaltyTiers" :key="t.id" class="text-xs text-slate-500">
                        {{ t.icon }} {{ t.name }}
                    </span>
                </div>
            </div>

            <!-- Transactions History -->
            <div class="card">
                <h3 class="text-xl font-bold text-white mb-6 pb-4 border-b border-white/10">Lịch sử giao dịch</h3>

                <div v-if="history.length" class="space-y-4">
                    <div v-for="tx in history" :key="tx.id" 
                        class="flex items-center justify-between py-3 border-b border-white/5 last:border-0">
                        <div>
                            <p class="font-medium text-white">{{ tx.description }}</p>
                            <p class="text-sm text-slate-500">{{ formatDate(tx.created_at) }}</p>
                        </div>
                        <span class="text-lg font-bold" 
                            :class="tx.type === 'earn' ? 'text-success' : 'text-error'">
                            {{ tx.type === 'earn' ? '+' : '-' }}{{ formatPoints(tx.points) }}
                        </span>
                    </div>
                </div>

                <div v-else class="text-center py-12 text-slate-500">
                    <p>Chưa có giao dịch nào.</p>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-16">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 mx-auto text-slate-600 mb-4" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="1">
                <path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4" />
                <path d="M4 6v12c0 1.1.9 2 2 2h14v-4" />
                <path d="M18 12a2 2 0 0 0-2 2c0 1.1.9 2 2 2h4v-4h-4z" />
            </svg>
            <h3 class="text-xl font-semibold text-slate-400 mb-2">Không thể tải thông tin điểm thưởng</h3>
            <p class="text-slate-500">Vui lòng đăng nhập để xem điểm thưởng của bạn</p>
        </div>
    </div>
</template>
