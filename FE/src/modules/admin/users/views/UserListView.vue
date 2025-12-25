<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import httpClient from '@/plugins/api/httpClient'

const { t } = useI18n()

interface User {
    id: number
    name: string
    email: string
    roles?: { id: number; name: string }[]
    is_active?: boolean
    created_at: string
}

const users = ref<User[]>([])
const isLoading = ref(true)
const currentPage = ref(1)
const totalPages = ref(1)
const search = ref('')

const formatDate = (date: string) => new Date(date).toLocaleDateString('vi-VN')

const fetchUsers = async () => {
    isLoading.value = true
    try {
        const params: any = { page: currentPage.value, per_page: 15 }
        if (search.value) params.search = search.value
        const response = await httpClient.get('/admin/users', { params })
        const data = response.data
        if (data?.data?.data && Array.isArray(data.data.data)) {
            users.value = data.data.data
            totalPages.value = data.data.last_page || 1
        } else if (Array.isArray(data?.data)) {
            users.value = data.data
        } else {
            users.value = []
        }
    } catch (error) {
        console.error('Failed to fetch users:', error)
        users.value = []
    } finally {
        isLoading.value = false
    }
}

onMounted(fetchUsers)
</script>

<template>
    <div class="h-full flex flex-col p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6 flex-shrink-0">
            <div>
                <h1 class="text-2xl font-bold text-white">{{ t('admin.users') }}</h1>
                <p class="text-slate-400 mt-1">Quản lý người dùng hệ thống</p>
            </div>
        </div>

        <!-- Search -->
        <div class="bg-dark-800 rounded-xl border border-white/10 p-4 mb-6 flex-shrink-0">
            <div class="flex gap-4">
                <div class="relative flex-1">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"
                        xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.3-4.3" />
                    </svg>
                    <input v-model="search" @keyup.enter="fetchUsers" type="text" class="form-input pl-10"
                        placeholder="Tìm kiếm người dùng..." />
                </div>
                <button @click="fetchUsers" class="btn btn-secondary">Tìm kiếm</button>
            </div>
        </div>

        <!-- Table Container -->
        <div class="flex-1 min-h-0 bg-dark-800 rounded-xl border border-white/10 overflow-hidden flex flex-col">
            <div v-if="isLoading" class="flex-1 flex items-center justify-center">
                <div class="inline-block w-8 h-8 border-4 border-primary/20 border-t-primary rounded-full animate-spin">
                </div>
            </div>

            <div v-else class="flex-1 overflow-auto">
                <table class="w-full">
                    <thead class="sticky top-0 z-10 bg-dark-700">
                        <tr class="border-b border-white/10">
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">ID</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Người dùng</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Vai trò</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Trạng thái</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Ngày tạo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="user in users" :key="user.id" class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4 text-sm text-slate-400">#{{ user.id }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-full bg-gradient-primary flex items-center justify-center text-white font-bold">
                                        {{ user.name?.charAt(0)?.toUpperCase() }}</div>
                                    <div>
                                        <p class="font-medium text-white">{{ user.name }}</p>
                                        <p class="text-xs text-slate-500">{{ user.email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    <span v-for="role in user.roles" :key="role.id"
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary-light">{{
                                        role.name }}</span>
                                    <span v-if="!user.roles?.length" class="text-slate-500">User</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    :class="['inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium', user.is_active !== false ? 'bg-success/10 text-success' : 'bg-error/10 text-error']">
                                    {{ user.is_active !== false ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-400">{{ formatDate(user.created_at) }}</td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="!users.length" class="py-16 text-center">
                    <p class="text-slate-400">Chưa có người dùng nào</p>
                </div>

                <!-- Pagination -->
                <div v-if="totalPages > 1"
                    class="sticky bottom-0 flex items-center justify-center gap-2 p-4 border-t border-white/10 bg-dark-800">
                    <button @click="currentPage--; fetchUsers()" :disabled="currentPage <= 1"
                        class="btn btn-secondary btn-sm">{{ t('common.previous') }}</button>
                    <span class="text-slate-400 text-sm">{{ currentPage }} / {{ totalPages }}</span>
                    <button @click="currentPage++; fetchUsers()" :disabled="currentPage >= totalPages"
                        class="btn btn-secondary btn-sm">{{ t('common.next') }}</button>
                </div>
            </div>
        </div>
    </div>
</template>
