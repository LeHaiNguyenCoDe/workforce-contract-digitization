<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/30" @click.self="$emit('close')">
        <div class="bg-white rounded-2xl w-[95%] sm:w-[480px] max-h-[85vh] flex flex-col shadow-2xl">
            <!-- Header -->
            <header class="flex justify-between items-center px-5 py-4 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-800">{{ t('common.chat.new_conversation') }}</h2>
                <button @click="$emit('close')"
                    class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </button>
            </header>

            <!-- Tabs -->
            <div class="flex px-4 border-b border-gray-100">
                <button :class="['flex-1 py-3 text-sm font-medium transition-all relative',
                    activeTab === 'private' ? 'text-teal-600' : 'text-gray-400 hover:text-gray-600']"
                    @click="activeTab = 'private'">
                    {{ t('common.chat.private_chat') }}
                    <span v-if="activeTab === 'private'"
                        class="absolute bottom-0 left-0 right-0 h-0.5 bg-teal-500 rounded-t" />
                </button>
                <button :class="['flex-1 py-3 text-sm font-medium transition-all relative',
                    activeTab === 'group' ? 'text-teal-600' : 'text-gray-400 hover:text-gray-600']"
                    @click="activeTab = 'group'">
                    {{ t('common.chat.create_group') }}
                    <span v-if="activeTab === 'group'"
                        class="absolute bottom-0 left-0 right-0 h-0.5 bg-teal-500 rounded-t" />
                </button>
            </div>

            <!-- Private Chat Tab -->
            <div v-if="activeTab === 'private'" class="flex-1 overflow-hidden flex flex-col p-4">
                <div class="relative mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.35-4.35" />
                    </svg>
                    <input v-model="searchQuery" type="text" :placeholder="t('common.chat.search_users')"
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-100 transition-all" />
                </div>

                <div class="flex-1 overflow-y-auto space-y-0.5">
                    <div v-if="isLoadingUsers" class="flex justify-center py-8">
                        <div class="w-6 h-6 border-2 border-gray-200 border-t-teal-500 rounded-full animate-spin" />
                    </div>
                    <div v-else-if="searchResults.length === 0" class="text-center py-8 text-gray-400 text-sm">
                        {{ t('common.chat.no_users_found') }}
                    </div>
                    <div v-for="user in searchResults" :key="user.id" @click="$emit('start-chat', user.id)"
                        class="flex items-center gap-3 p-3 rounded-xl cursor-pointer hover:bg-gray-50 transition-all">
                        <div class="relative">
                            <img v-if="user.avatar" :src="user.avatar" :alt="user.name"
                                class="w-10 h-10 rounded-full object-cover" />
                            <div v-else
                                :class="['w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold text-white', getAvatarColor(user.id)]">
                                {{ getInitials(user.name) }}
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-700 truncate">{{ user.name }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ user.email }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Group Chat Tab -->
            <div v-if="activeTab === 'group'" class="flex-1 overflow-hidden flex flex-col p-4">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-600 mb-2">{{ t('common.chat.group_name')
                        }}</label>
                    <input v-model="groupName" type="text" :placeholder="t('common.chat.enter_group_name')"
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-100 transition-all" />
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-600 mb-2">{{ t('common.chat.add_members')
                        }}</label>
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8" />
                            <path d="m21 21-4.35-4.35" />
                        </svg>
                        <input v-model="memberSearchQuery" type="text" :placeholder="t('common.chat.search_to_add')"
                            class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-100 transition-all" />
                    </div>
                </div>

                <!-- Selected Members -->
                <div v-if="selectedMembers.length > 0" class="flex flex-wrap gap-2 mb-4">
                    <div v-for="member in selectedMembers" :key="member.id"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-teal-50 text-teal-700 rounded-full text-sm font-medium">
                        <span>{{ member.name }}</span>
                        <button @click="removeMember(member.id)" class="hover:text-teal-900 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18" />
                                <line x1="6" y1="6" x2="18" y2="18" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Search Results -->
                <div class="flex-1 overflow-y-auto space-y-0.5">
                    <div v-if="isLoadingUsers" class="flex justify-center py-8">
                        <div class="w-6 h-6 border-2 border-gray-200 border-t-teal-500 rounded-full animate-spin" />
                    </div>
                    <div v-for="user in memberSearchResults" :key="user.id" @click="toggleMember(user)" :class="['flex items-center gap-3 p-3 rounded-xl cursor-pointer transition-all',
                        isSelected(user.id) ? 'bg-teal-50' : 'hover:bg-gray-50']">
                        <div class="relative">
                            <img v-if="user.avatar" :src="user.avatar" :alt="user.name"
                                class="w-10 h-10 rounded-full object-cover" />
                            <div v-else
                                :class="['w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold text-white', getAvatarColor(user.id)]">
                                {{ getInitials(user.name) }}
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-700 truncate">{{ user.name }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ user.email }}</p>
                        </div>
                        <div v-if="isSelected(user.id)" class="text-teal-500">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Create Button -->
                <button @click="handleCreateGroup" :disabled="!canCreateGroup" class="w-full mt-4 py-3 rounded-xl text-sm font-medium transition-all
                           bg-teal-500 text-white hover:bg-teal-600
                           disabled:opacity-50 disabled:cursor-not-allowed shadow-sm">
                    {{ t('common.chat.create') }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { FriendService } from '../services/FriendService'
import type { IUser } from '../models/Chat'

const emit = defineEmits<{
    (e: 'close'): void
    (e: 'start-chat', userId: number): void
    (e: 'create-group', name: string, memberIds: number[], avatar?: File): void
}>()

const { t } = useI18n()

const avatarColors = ['bg-teal-500', 'bg-blue-500', 'bg-purple-500', 'bg-pink-500', 'bg-orange-500', 'bg-amber-500', 'bg-emerald-500', 'bg-cyan-500']

const activeTab = ref<'private' | 'group'>('private')
const allUsers = ref<IUser[]>([])
const isLoadingUsers = ref(false)
const searchQuery = ref('')
const groupName = ref('')
const memberSearchQuery = ref('')
const selectedMembers = ref<IUser[]>([])

const searchResults = computed(() => {
    if (!searchQuery.value.trim()) return allUsers.value
    const query = searchQuery.value.toLowerCase()
    return allUsers.value.filter(user =>
        user.name.toLowerCase().includes(query) ||
        (user.email && user.email.toLowerCase().includes(query))
    )
})

const memberSearchResults = computed(() => {
    if (!memberSearchQuery.value.trim()) return allUsers.value
    const query = memberSearchQuery.value.toLowerCase()
    return allUsers.value.filter(user =>
        user.name.toLowerCase().includes(query) ||
        (user.email && user.email.toLowerCase().includes(query))
    )
})

const canCreateGroup = computed(() => groupName.value.trim().length > 0 && selectedMembers.value.length > 0)

function getAvatarColor(id: number): string {
    return avatarColors[id % avatarColors.length]
}

function getInitials(name: string): string {
    return name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase()
}

async function loadAllUsers() {
    isLoadingUsers.value = true
    try {
        allUsers.value = await FriendService.getAllUsers()
    } catch (error) {
        console.error('[Chat] Failed to load users:', error)
        allUsers.value = []
    } finally {
        isLoadingUsers.value = false
    }
}

function isSelected(userId: number): boolean {
    return selectedMembers.value.some(m => m.id === userId)
}

function toggleMember(user: IUser) {
    if (isSelected(user.id)) {
        removeMember(user.id)
    } else {
        selectedMembers.value.push(user)
    }
}

function removeMember(userId: number) {
    selectedMembers.value = selectedMembers.value.filter(m => m.id !== userId)
}

function handleCreateGroup() {
    if (!canCreateGroup.value) return
    emit('create-group', groupName.value.trim(), selectedMembers.value.map(m => m.id))
}

onMounted(() => loadAllUsers())
</script>
