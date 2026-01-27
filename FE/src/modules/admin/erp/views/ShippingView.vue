<script setup lang="ts">
import { ref, onMounted } from 'vue'
import httpClient from '@/plugins/api/httpClient'

interface ShippingPartner {
    id: number
    name: string
    code: string
    logo?: string
    api_url?: string
    is_active: boolean
}

const partners = ref<ShippingPartner[]>([])
const isLoading = ref(true)

const fetchPartners = async () => {
    isLoading.value = true
    try {
        const res = await httpClient.get('admin/shipping/partners')
        partners.value = res.data.data || []
    } catch (e) {
        console.error(e)
    } finally {
        isLoading.value = false
    }
}

const togglePartner = async (id: number) => {
    try {
        await httpClient.post(`admin/shipping/partners/${id}/toggle`)
        fetchPartners()
    } catch (e) {
        console.error(e)
    }
}

onMounted(fetchPartners)
</script>

<template>
    <div>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-white">Đối tác vận chuyển</h1>
        </div>

        <div v-if="isLoading" class="card p-8 text-center text-slate-400">Đang tải...</div>
        
        <div v-else class="grid md:grid-cols-3 gap-4">
            <div v-for="partner in partners" :key="partner.id" 
                class="card flex items-center gap-4 p-4">
                <div class="w-14 h-14 rounded-xl flex items-center justify-center text-xl font-bold"
                    :class="partner.is_active ? 'bg-success/20 text-success' : 'bg-dark-600 text-slate-500'">
                    {{ partner.code.toUpperCase().slice(0, 3) }}
                </div>
                <div class="flex-1">
                    <div class="font-medium text-white">{{ partner.name }}</div>
                    <div class="text-sm text-slate-500">{{ partner.api_url || 'Chưa cấu hình API' }}</div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" :checked="partner.is_active" @change="togglePartner(partner.id)" class="sr-only peer">
                    <div class="w-11 h-6 bg-dark-600 peer-focus:ring-2 peer-focus:ring-primary/50 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                </label>
            </div>
        </div>

        <div class="card mt-6 p-6 bg-gradient-to-r from-info/10 to-transparent border border-info/20">
            <h3 class="font-bold text-white mb-2">💡 Tích hợp API vận chuyển</h3>
            <p class="text-slate-400 text-sm">
                Để tích hợp với GHN, GHTK hoặc Viettel Post, bạn cần đăng ký tài khoản đối tác và lấy API key từ trang quản trị của họ.
                Sau đó nhập API key vào cấu hình tương ứng.
            </p>
        </div>
    </div>
</template>
