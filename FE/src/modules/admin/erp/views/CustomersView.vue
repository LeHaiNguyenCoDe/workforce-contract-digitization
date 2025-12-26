<script setup lang="ts">
import { ref, onMounted } from 'vue'
const isLoading = ref(true)
onMounted(() => { setTimeout(() => { isLoading.value = false }, 500) })
</script>

<template>
    <div class="h-full flex flex-col p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-white">Quản lý Khách hàng</h1>
                <p class="text-slate-400 mt-1">Danh sách khách hàng và lịch sử mua hàng</p>
            </div>
            <button class="btn btn-primary">+ Thêm khách hàng</button>
        </div>
        <div class="flex-1 bg-dark-800 rounded-xl border border-white/10 flex items-center justify-center">
            <div class="text-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1" class="mx-auto text-slate-600 mb-4">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
                <h3 class="text-xl font-semibold text-white mb-2">Khách hàng</h3>
                <p class="text-slate-400">Quản lý thông tin và phân tích hành vi khách hàng. Module đang phát triển.</p>
            </div>
        </div>
    </div>
</template>
