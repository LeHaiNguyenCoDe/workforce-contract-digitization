<script setup lang="ts">
/**
 * Promotion List View
 * Uses usePromotions composable for logic separation
 */
import { RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'
const { t } = useI18n()

// Use composable for all promotion logic
const {
    promotions,
    isLoading,
    formatDiscount
} = useLandingPromotions()

// Format date helper
const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('vi-VN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    })
}
</script>

<template>
    <div class="container py-8">
        <!-- Header -->
        <div class="relative text-center max-w-2xl mx-auto mb-12 py-8">
            <div class="absolute inset-0 bg-gradient-primary opacity-10 rounded-3xl blur-3xl"></div>

            <div class="relative">
                <span
                    class="inline-block px-4 py-1 text-sm font-semibold text-secondary bg-secondary/10 rounded-full mb-4">
                    🎉 Khuyến mãi đặc biệt
                </span>
                <h1 class="text-3xl md:text-4xl font-bold text-white mb-4">{{ t('nav.promotions') }}</h1>
                <p class="text-slate-400">Khám phá các chương trình khuyến mãi hấp dẫn nhất</p>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="isLoading" class="grid md:grid-cols-2 gap-6">
            <div v-for="i in 4" :key="i" class="h-48 bg-dark-700 rounded-2xl animate-pulse"></div>
        </div>

        <!-- Promotions Grid -->
        <div v-else-if="promotions.length" class="grid md:grid-cols-2 gap-6">
            <RouterLink v-for="promo in promotions" :key="promo.id" :to="`/promotions/${promo.id}`"
                class="group relative overflow-hidden rounded-2xl border border-white/10 hover:border-primary/50 transition-all duration-300">
                <div class="absolute inset-0 bg-gradient-to-br from-primary/20 to-secondary/20 opacity-50"></div>

                <div class="relative p-6 md:p-8">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <div
                                class="inline-block px-3 py-1 text-lg font-bold text-white bg-gradient-primary rounded-full mb-4">
                                Giảm {{ formatDiscount(promo) }}
                            </div>

                            <h3
                                class="text-xl font-bold text-white mb-2 group-hover:text-primary-light transition-colors">
                                {{ promo.name }}
                            </h3>

                            <p class="text-slate-400 text-sm mb-4 line-clamp-2">{{ promo.description }}</p>

                            <div class="flex items-center gap-2 text-sm text-slate-500">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10" />
                                    <polyline points="12 6 12 12 16 14" />
                                </svg>
                                <span>Đến {{ formatDate(promo.end_date) }}</span>
                            </div>
                        </div>

                        <div
                            class="w-16 h-16 flex items-center justify-center rounded-2xl bg-white/10 text-secondary group-hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="9" cy="9" r="2" />
                                <circle cx="15" cy="15" r="2" />
                                <path d="M7.5 16.5 16.5 7.5" />
                            </svg>
                        </div>
                    </div>
                </div>
            </RouterLink>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-16">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 mx-auto text-slate-600 mb-4" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="1">
                <circle cx="9" cy="9" r="2" />
                <circle cx="15" cy="15" r="2" />
                <path d="M7.5 16.5 16.5 7.5" />
            </svg>
            <h3 class="text-xl font-semibold text-slate-400 mb-2">Chưa có khuyến mãi</h3>
            <p class="text-slate-500">Hãy quay lại sau để xem các chương trình khuyến mãi mới</p>
        </div>
    </div>
</template>
