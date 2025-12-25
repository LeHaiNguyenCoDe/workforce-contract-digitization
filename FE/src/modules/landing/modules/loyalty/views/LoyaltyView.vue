<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import apiClient from '@/plugins/api/httpClient'
import type { LoyaltyAccount, ApiResponse } from '@/api/types'

const loyalty = ref<LoyaltyAccount | null>(null)
const isLoading = ref(true)

const tierConfig: Record<string, { label: string; color: string }> = {
    bronze: { label: 'Đồng', color: '#CD7F32' },
    silver: { label: 'Bạc', color: '#C0C0C0' },
    gold: { label: 'Vàng', color: '#FFD700' },
    platinum: { label: 'Bạch Kim', color: '#E5E4E2' }
}

const tierInfo = computed(() => loyalty.value ? tierConfig[loyalty.value.tier] : null)

const formatDate = (date: string) => new Date(date).toLocaleDateString('vi-VN')

const fetchLoyalty = async () => {
    isLoading.value = true
    try {
        const response = await apiClient.get<ApiResponse<LoyaltyAccount>>('/frontend/loyalty')
        loyalty.value = response.data.data || null
    } catch (error) {
        console.error('Failed to fetch loyalty:', error)
    } finally {
        isLoading.value = false
    }
}

onMounted(fetchLoyalty)
</script>

<template>
    <div class="loyalty-page">
        <div class="container">
            <h1>Điểm thưởng</h1>

            <div v-if="isLoading" class="skeleton-block"></div>

            <div v-else-if="loyalty" class="loyalty-content">
                <div class="loyalty-summary card">
                    <div class="points-display">
                        <span class="points-value">{{ loyalty.points.toLocaleString() }}</span>
                        <span class="points-label">điểm</span>
                    </div>
                    <div class="tier-badge" :style="{ background: tierInfo?.color }">
                        {{ tierInfo?.label }}
                    </div>
                </div>

                <div class="transactions card">
                    <h3>Lịch sử giao dịch</h3>

                    <div v-if="loyalty.transactions.length" class="transactions-list">
                        <div v-for="tx in loyalty.transactions" :key="tx.id" class="transaction-item">
                            <div class="tx-info">
                                <span class="tx-desc">{{ tx.description }}</span>
                                <span class="tx-date">{{ formatDate(tx.created_at) }}</span>
                            </div>
                            <span class="tx-points" :class="tx.type">
                                {{ tx.type === 'earn' ? '+' : '-' }}{{ tx.points }}
                            </span>
                        </div>
                    </div>

                    <div v-else class="no-transactions">
                        <p>Chưa có giao dịch nào.</p>
                    </div>
                </div>
            </div>

            <div v-else class="empty-state">
                <p>Không thể tải thông tin điểm thưởng.</p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.loyalty-page h1 {
    margin-bottom: var(--space-8);
}

.skeleton-block {
    height: 400px;
    background: var(--color-bg-tertiary);
    border-radius: var(--radius-lg);
    animation: pulse 2s infinite;
}

.loyalty-content {
    display: flex;
    flex-direction: column;
    gap: var(--space-6);
}

.loyalty-summary {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--gradient-primary);
    border: none;
}

.points-display {
    text-align: center;
}

.points-value {
    display: block;
    font-size: var(--text-5xl);
    font-weight: 800;
    color: white;
}

.points-label {
    font-size: var(--text-lg);
    color: rgba(255, 255, 255, 0.8);
}

.tier-badge {
    padding: var(--space-2) var(--space-6);
    font-weight: 700;
    color: #1a1a2e;
    border-radius: var(--radius-full);
}

.transactions h3 {
    margin-bottom: var(--space-4);
    padding-bottom: var(--space-4);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.transactions-list {
    display: flex;
    flex-direction: column;
}

.transaction-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-3) 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.tx-info {
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
}

.tx-desc {
    font-weight: 500;
}

.tx-date {
    font-size: var(--text-xs);
    color: var(--color-text-muted);
}

.tx-points {
    font-weight: 700;
    font-size: var(--text-lg);
}

.tx-points.earn {
    color: var(--color-success);
}

.tx-points.redeem {
    color: var(--color-error);
}

.no-transactions {
    text-align: center;
    padding: var(--space-8);
    color: var(--color-text-muted);
}

.empty-state {
    text-align: center;
    padding: var(--space-16);
    color: var(--color-text-muted);
}
</style>
