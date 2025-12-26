<script setup lang="ts">
import { ref, onMounted } from 'vue'
const isLoading = ref(true)
onMounted(() => { setTimeout(() => { isLoading.value = false }, 500) })
</script>

<template>
    <div class="h-full flex flex-col p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-white">Nhật ký hệ thống</h1>
                <p class="text-slate-400 mt-1">Xem lịch sử thao tác: ai làm gì, lúc nào, thay đổi gì</p>
            </div>
            <button class="btn btn-secondary">Xuất log</button>
        </div>
        <div class="flex-1 bg-dark-800 rounded-xl border border-white/10 flex items-center justify-center">
            <div class="text-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1" class="mx-auto text-slate-600 mb-4">
                    <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z" />
                    <polyline points="14 2 14 8 20 8" />
                    <line x1="16" x2="8" y1="13" y2="13" />
                    <line x1="16" x2="8" y1="17" y2="17" />
                    <line x1="10" x2="8" y1="9" y2="9" />
                </svg>
                <h3 class="text-xl font-semibold text-white mb-2">Audit Log</h3>
                <p class="text-slate-400">Theo dõi mọi thao tác quan trọng trên hệ thống. Module đang phát triển.</p>
            </div>
        </div>
    </div>
</template>
