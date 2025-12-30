<script setup lang="ts">
/**
 * Promotion Detail View
 * Uses usePromotions composable for logic and formatting
 */
import { ref, onMounted } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'
import httpClient from '@/plugins/api/httpClient'

const { t } = useI18n()
const route = useRoute()

// Use composable
const { formatDiscount } = useLandingPromotions()

const promo = ref<any>(null)
const isLoading = ref(true)

const fetchPromotion = async () => {
    try {
        const response = await httpClient.get(`/frontend/promotions/${route.params.id}`)
        const data = response.data as any
        promo.value = data?.data || data
    } catch (error) {
        console.error('Failed to fetch promotion:', error)
    } finally {
        isLoading.value = false
    }
}

const formatDate = (date: string) => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString('vi-VN', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    })
}

const copyCode = () => {
    if (promo.value?.code) {
        navigator.clipboard.writeText(promo.value.code)
    }
}

onMounted(fetchPromotion)
</script>

<template>
    <div class="container py-8">
        <!-- Loading -->
        <div v-if="isLoading" class="max-w-2xl mx-auto animate-pulse">
            <div class="h-12 bg-dark-700 rounded-lg w-1/2 mx-auto mb-8"></div>
            <div class="h-48 bg-dark-700 rounded-2xl"></div>
        </div>

        <!-- Promotion -->
        <div v-else-if="promo" class="max-w-2xl mx-auto">
            <!-- Breadcrumb -->
            <nav class="flex items-center gap-2 text-sm text-slate-400 mb-8">
                <RouterLink to="/" class="hover:text-white">{{ t('nav.home') }}</RouterLink>
                <span>/</span>
                <RouterLink to="/promotions" class="hover:text-white">{{ t('nav.promotions') }}</RouterLink>
                <span>/</span>
                <span class="text-white">{{ promo.name }}</span>
            </nav>

            <!-- Card -->
            <div class="card relative overflow-hidden">
                <!-- Background -->
                <div class="absolute inset-0 bg-gradient-to-br from-primary/20 to-secondary/20 opacity-50"></div>

                <div class="relative text-center py-8">
                    <!-- Badge -->
                    <div
                        class="inline-block px-6 py-3 text-3xl font-bold text-white bg-gradient-primary rounded-2xl mb-6 shadow-lg shadow-primary/25">
                        {{ t('common.discountAmount') }} {{ formatDiscount(promo) }}
                    </div>

                    <h1 class="text-2xl md:text-3xl font-bold text-white mb-4">{{ promo.name }}</h1>
                    <p class="text-slate-400 mb-8 max-w-lg mx-auto">{{ promo.description }}</p>

                    <!-- Code -->
                    <div v-if="promo.code" class="mb-8">
                        <p class="text-sm text-slate-400 mb-2">{{ t('common.promoCode') }}</p>
                        <div
                            class="inline-flex items-center gap-3 px-6 py-3 bg-dark-700 rounded-xl border border-dashed border-primary/50">
                            <span class="text-xl font-mono font-bold text-primary">{{ promo.code }}</span>
                            <button @click="copyCode"
                                class="p-2 text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2">
                                    <rect width="14" height="14" x="8" y="8" rx="2" />
                                    <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Dates -->
                    <div class="flex items-center justify-center gap-6 text-sm text-slate-400">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <rect width="18" height="18" x="3" y="4" rx="2" />
                                <path d="M16 2v4" />
                                <path d="M8 2v4" />
                                <path d="M3 10h18" />
                            </svg>
                            <span>{{ t('common.from') }} {{ formatDate(promo.starts_at) }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 16 14" />
                            </svg>
                            <span>{{ t('common.until') }} {{ formatDate(promo.ends_at) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CTA -->
            <div class="text-center mt-8">
                <RouterLink to="/products" class="btn btn-primary btn-lg">
                    {{ t('common.shopNow') }}
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14" />
                        <path d="m12 5 7 7-7 7" />
                    </svg>
                </RouterLink>
            </div>
        </div>

        <!-- Not Found -->
        <div v-else class="text-center py-16">
            <h2 class="text-2xl font-bold text-slate-400 mb-4">{{ t('common.promoNotFound') }}</h2>
            <RouterLink to="/promotions" class="btn btn-primary">{{ t('common.backToList') }}</RouterLink>
        </div>
    </div>
</template>
