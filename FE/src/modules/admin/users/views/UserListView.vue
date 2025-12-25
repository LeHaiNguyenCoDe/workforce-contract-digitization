<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useUserStore } from '../store/store'
import { useUsers } from '../composables/useUsers'

const { t } = useI18n()

// Store
const store = useUserStore()

// Composables
const {
  searchQuery,
  filteredUsers,
  formatDate,
  setSearchQuery,
  changePage
} = useUsers()

// Computed from store
const users = computed(() => store.users)
const isLoading = computed(() => store.isLoading)
const currentPage = computed(() => store.currentPage)
const totalPages = computed(() => store.totalPages)

// Lifecycle
onMounted(async () => {
  await store.fetchUsers()
})
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
          <input :value="searchQuery" @input="setSearchQuery(($event.target as HTMLInputElement).value)"
            @keyup.enter="store.fetchUsers({ search: searchQuery })" type="text" class="form-input pl-10"
            placeholder="Tìm kiếm người dùng..." />
        </div>
        <button @click="store.fetchUsers({ search: searchQuery })" class="btn btn-secondary">Tìm kiếm</button>
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
            <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-white/5 transition-colors">
              <td class="px-6 py-4 text-sm text-slate-400">#{{ user.id }}</td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div
                    class="w-10 h-10 rounded-full bg-gradient-primary flex items-center justify-center text-white font-bold">
                    {{ user.name?.charAt(0)?.toUpperCase() }}
                  </div>
                  <div>
                    <p class="font-medium text-white">{{ user.name }}</p>
                    <p class="text-xs text-slate-500">{{ user.email }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="flex flex-wrap gap-1">
                  <span v-for="role in user.roles" :key="role.id"
                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary-light">
                    {{ role.name }}
                  </span>
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
        <div v-if="!filteredUsers.length" class="py-16 text-center">
          <p class="text-slate-400">Chưa có người dùng nào</p>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1"
          class="sticky bottom-0 flex items-center justify-center gap-2 p-4 border-t border-white/10 bg-dark-800">
          <button @click="changePage(currentPage - 1)" :disabled="currentPage <= 1"
            class="btn btn-secondary btn-sm" :class="{ 'opacity-50 cursor-not-allowed': currentPage <= 1 }">
            {{ t('common.previous') }}
          </button>
          <span class="text-slate-400 text-sm">{{ currentPage }} / {{ totalPages }}</span>
          <button @click="changePage(currentPage + 1)" :disabled="currentPage >= totalPages"
            class="btn btn-secondary btn-sm"
            :class="{ 'opacity-50 cursor-not-allowed': currentPage >= totalPages }">
            {{ t('common.next') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
