<script setup lang="ts">
import { ref, onMounted } from 'vue'
const isLoading = ref(true)
onMounted(() => { setTimeout(() => { isLoading.value = false }, 500) })
</script>

<template>
    <div class="h-full flex flex-col p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-white">Trả hàng / RMA</h1>
                <p class="text-slate-400 mt-1">Xử lý trả hàng, kiểm tra và hoàn tiền</p>
            </div>
            <button class="btn btn-primary">+ Tạo phiếu RMA</button>
        </div>
        <div class="flex-1 bg-dark-800 rounded-xl border border-white/10 flex items-center justify-center">
            <div class="text-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1" class="mx-auto text-slate-600 mb-4">
                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                    <polyline points="9 22 9 12 15 12 15 22" />
                </svg>
                <h3 class="text-xl font-semibold text-white mb-2">Quản lý trả hàng</h3>
                <p class="text-slate-400">Tiếp nhận, kiểm tra và xử lý trả hàng. Module đang phát triển.</p>
            </div>
        </div>
    </div>
</template>
