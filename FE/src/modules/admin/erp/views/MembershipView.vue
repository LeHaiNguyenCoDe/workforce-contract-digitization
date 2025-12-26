<script setup lang="ts">
import { ref, onMounted } from 'vue'
const isLoading = ref(true)
onMounted(() => { setTimeout(() => { isLoading.value = false }, 500) })
</script>

<template>
    <div class="h-full flex flex-col p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-white">Hạng thành viên</h1>
                <p class="text-slate-400 mt-1">Quản lý hạng Đồng, Bạc, Vàng và quyền lợi</p>
            </div>
            <button class="btn btn-primary">+ Thêm hạng</button>
        </div>
        <div class="flex-1 bg-dark-800 rounded-xl border border-white/10 flex items-center justify-center">
            <div class="text-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1" class="mx-auto text-slate-600 mb-4">
                    <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6" />
                    <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18" />
                    <path d="M4 22h16" />
                    <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22" />
                    <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22" />
                    <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z" />
                </svg>
                <h3 class="text-xl font-semibold text-white mb-2">Loyalty & Membership</h3>
                <p class="text-slate-400">Cấu hình hạng thành viên và chiết khấu tự động. Module đang phát triển.</p>
            </div>
        </div>
    </div>
</template>
