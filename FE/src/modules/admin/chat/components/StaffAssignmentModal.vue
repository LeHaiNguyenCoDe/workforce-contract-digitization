<template>
  <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden animate-in fade-in zoom-in duration-200">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-800">Phân công nhân viên</h3>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
      </div>

      <!-- Search -->
      <div class="p-4">
        <div class="relative">
          <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"></circle>
            <path d="m21 21-4.35-4.35"></path>
          </svg>
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Tìm kiếm nhân viên..." 
            class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100 transition-all"
          />
        </div>
      </div>

      <!-- List -->
      <div class="max-h-64 overflow-y-auto px-2 pb-4">
        <div v-if="loading" class="flex justify-center py-8">
          <div class="w-6 h-6 border-2 border-gray-200 border-t-teal-500 rounded-full animate-spin"></div>
        </div>
        <div v-else-if="filteredUsers.length === 0" class="text-center py-8 text-gray-400 text-sm">
          Không tìm thấy nhân viên nào
        </div>
        <div v-else class="space-y-1">
          <button 
            v-for="user in filteredUsers" 
            :key="user.id"
            @click="handleSelect(user.id)"
            class="w-full flex items-center gap-3 p-3 rounded-xl hover:bg-teal-50 transition-all text-left group"
          >
            <div class="relative flex-shrink-0">
              <img v-if="user.avatar" :src="user.avatar" :alt="user.name" class="w-10 h-10 rounded-full object-cover" />
              <div v-else class="w-10 h-10 rounded-full bg-teal-500 flex items-center justify-center text-white font-medium">
                {{ user.name.charAt(0).toUpperCase() }}
              </div>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-800 truncate group-hover:text-teal-600">{{ user.name }}</p>
              <p class="text-xs text-gray-400 truncate">{{ user.email }}</p>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-teal-500 opacity-0 group-hover:opacity-100 transition-opacity" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { FriendService } from '../services/FriendService'
import type { IUser } from '../models/Chat'

const props = defineProps<{
  staffOnly?: boolean
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'select', userId: number): void
}>()

const users = ref<IUser[]>([])
const loading = ref(false)
const searchQuery = ref('')

const filteredUsers = computed(() => {
  if (!searchQuery.value) return users.value
  const q = searchQuery.value.toLowerCase()
  return users.value.filter(u => 
    u.name.toLowerCase().includes(q) || 
    (u.email && u.email.toLowerCase().includes(q))
  )
})

async function loadUsers() {
  loading.value = true
  try {
    const data = await FriendService.getAllUsers()
    users.value = data
  } catch (error) {
    console.error('Failed to load users:', error)
  } finally {
    loading.value = false
  }
}

function handleSelect(userId: number) {
  emit('select', userId)
}

onMounted(loadUsers)
</script>
