<script setup lang="ts">
import { RouterView, RouterLink, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores'

const route = useRoute()
// @ts-ignore
const authStore = useAuthStore()

const tabs = [
    {
        path: '/profile/cart',
        icon: 'shop-cart',
        label: 'Giỏ hàng của tôi',
        svg: `<svg width="40" height="40" viewBox="0 0 49 43" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M1 1.5H8.5L13.5 32.5H41.5L46.5 11.5H11" stroke="#8C9CA6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="16.5" cy="38.5" r="3" fill="#8C9CA6"/>
            <circle cx="38.5" cy="38.5" r="3" fill="#8C9CA6"/>
        </svg>`
    },
    {
        path: '/profile/info',
        icon: 'info',
        label: 'Thông tin của tôi',
        svg: `<svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect width="40" height="40" rx="4" fill="#6EB1E6"/>
            <path d="M12 12H28M12 18H28M12 24H20" stroke="white" stroke-width="2" stroke-linecap="round"/>
        </svg>`
    },
    {
        path: '/profile/address',
        icon: 'address',
        label: 'Địa chỉ nhận hàng',
        svg: `<svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M20 5L7 15V35H15V25H25V35H33V15L20 5Z" fill="#F0F0F5" stroke="#E54335" stroke-width="2"/>
            <path d="M17 10L20 7L23 10H17Z" fill="#E54335"/>
        </svg>`
    },
    {
        path: '/profile/payment',
        icon: 'payment',
        label: 'Phương thức thanh toán',
        svg: `<svg width="40" height="40" viewBox="0 0 40 28" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M33.3 0H6.7C3 0 0 3 0 6.7V21.3C0 25 3 28 6.7 28H33.3C37 28 40 25 40 21.3V6.7C40 3 37 0 33.3 0Z" fill="#253B80"/>
            <path d="M14.6 18.7L15.3 14H18.7C21.4 14 22.8 15.3 22.8 17.5C22.8 20.3 20.7 21.6 18 21.6H16.6C16.1 21.6 15.7 21.2 15.6 20.7L14.6 18.7Z" fill="white" opacity="0.6"/>
            <path d="M22.8 8.4C22.8 6.2 21.4 4.9 18.7 4.9H13.6C13.1 4.9 12.7 5.3 12.6 5.8L10.3 19.5C10.2 20 10.6 20.5 11.2 20.5H14.1C14.7 20.5 15.2 20.1 15.3 19.6L16 15.4H18.7C21.4 15.4 22.8 14.1 22.8 11.9V8.4Z" fill="white"/>
        </svg>`
    }
]

const isActiveTab = (path: string) => {
    return route.path === path || route.path.startsWith(path + '/')
}
</script>

<template>
    <div class="profile-layout bg-white min-h-screen">
        <div class="max-w-[1200px] mx-auto px-4 py-6">
            <!-- Breadcrumb -->
            <nav class="flex items-center gap-2 text-sm text-gray-500 mb-8">
                <RouterLink to="/" class="hover:text-[#9F7A5F] transition-colors">Home</RouterLink>
                <span>/</span>
                <RouterLink to="/profile/info" class="hover:text-[#9F7A5F] transition-colors">Tài khoản</RouterLink>
                <span>/</span>
                <span class="text-gray-400">{{tabs.find(t => isActiveTab(t.path))?.label || 'Tài khoản'}}</span>
            </nav>

            <!-- Tab Navigation -->
            <div class="flex border-b border-gray-200 mb-8 bg-white overflow-hidden rounded-t-lg">
                <RouterLink v-for="tab in tabs" :key="tab.path" :to="tab.path"
                    class="flex-1 flex items-center justify-center gap-3 py-4 px-6 border border-transparent transition-all duration-300 group"
                    :class="[
                        isActiveTab(tab.path)
                            ? 'bg-[#F2F2F2] border-[#D9D9D9] !border-b-transparent text-black z-10'
                            : 'bg-white text-gray-600 hover:bg-gray-50 border-b-gray-200'
                    ]">
                    <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center" v-html="tab.svg"></div>
                    <span class="font-medium text-[15px] hidden md:block">{{ tab.label }}</span>
                </RouterLink>
            </div>

            <!-- Content Container -->
            <div class="profile-content border border-[#D9D9D9] rounded-lg p-8 min-h-[500px] md:p-8 p-6">
                <RouterView v-slot="{ Component }">
                    <transition name="fade" mode="out-in">
                        <component :is="Component" />
                    </transition>
                </RouterView>
            </div>
        </div>
    </div>
</template>
