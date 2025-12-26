<script setup lang="ts">
import { ref, onMounted } from 'vue'
const isLoading = ref(true)
onMounted(() => { setTimeout(() => { isLoading.value = false }, 500) })
</script>

<template>
    <div class="h-full flex flex-col p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-white">Marketing Automation</h1>
                <p class="text-slate-400 mt-1">Gửi tin nhắn tự động: đơn giao, sinh nhật, inactive...</p>
            </div>
            <button class="btn btn-primary">+ Tạo automation</button>
        </div>
        <div class="flex-1 bg-dark-800 rounded-xl border border-white/10 flex items-center justify-center">
            <div class="text-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1" class="mx-auto text-slate-600 mb-4">
                    <path d="m22 8-6 4 6 4V8Z" />
                    <rect width="14" height="12" x="2" y="6" rx="2" ry="2" />
                </svg>
                <h3 class="text-xl font-semibold text-white mb-2">Automation</h3>
                <p class="text-slate-400">Tự động gửi email/SMS theo trigger. Module đang phát triển.</p>
            </div>
        </div>
    </div>
</template>
