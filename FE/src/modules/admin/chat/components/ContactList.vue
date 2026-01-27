<template>
    <div class="flex-1 overflow-y-auto">
        <!-- Loading -->
        <div v-if="loading" class="flex justify-center py-8">
            <div class="w-6 h-6 border-2 border-gray-200 border-t-teal-500 rounded-full animate-spin" />
        </div>

        <!-- Empty -->
        <div v-else-if="users.length === 0" class="flex flex-col items-center justify-center h-full p-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-gray-300 mb-3" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="1.5">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                <circle cx="9" cy="7" r="4" />
                <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
            </svg>
            <p class="text-gray-400 text-sm">{{ t('common.chat.no_users_found') }}</p>
        </div>

        <!-- Users grouped alphabetically -->
        <div v-else class="p-2">
            <template v-for="(group, letter) in groupedUsers" :key="letter">
                <div class="px-3 py-1.5 text-xs font-medium text-gray-400 sticky top-0 bg-white">
                    {{ letter }}
                </div>
                <div v-for="user in group" :key="user.id"
                    class="flex items-center gap-3 px-3 py-2.5 mx-1 my-0.5 rounded-lg hover:bg-gray-50 transition-all">
                    <!-- Avatar -->
                    <div class="relative flex-shrink-0">
                        <img v-if="user.avatar" :src="user.avatar" :alt="user.name"
                            class="w-10 h-10 rounded-full object-cover" />
                        <div v-else
                            :class="['w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-medium', getAvatarColor(user.id)]">
                            {{ getInitials(user.name) }}
                        </div>
                        <!-- Online indicator -->
                        <div v-if="user.is_online"
                            class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white" />
                    </div>

                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-medium text-gray-700 truncate">{{ user.name }}</h4>
                        <p class="text-xs text-gray-400 truncate">{{ user.email }}</p>
                    </div>

                    <!-- Actions based on friendship status -->
                    <div class="flex items-center gap-1.5 flex-shrink-0">
                        <!-- Start Chat (only if accepted friend) -->
                        <button v-if="user.friendship_status === 'accepted'" 
                            @click.stop="$emit('start-chat', user.id)"
                            class="p-2 rounded-lg text-gray-400 hover:text-teal-500 hover:bg-teal-50 transition-all"
                            :title="t('common.chat.start_chat')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                            </svg>
                        </button>

                        <!-- Accepted friend indicator -->
                        <div v-if="user.friendship_status === 'accepted'" 
                            class="p-2 text-green-500" 
                            title="Bạn bè">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                <circle cx="8.5" cy="7" r="4" />
                                <polyline points="17 11 19 13 23 9" />
                            </svg>
                        </div>

                        <!-- Pending request (received) - Accept/Reject -->
                        <template v-else-if="user.friendship_status === 'pending'">
                            <button @click.stop="$emit('accept-friend', user.friendship_id)"
                                class="p-2 rounded-lg text-gray-400 hover:text-green-500 hover:bg-green-50 transition-all"
                                title="Chấp nhận">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                            </button>
                            <button @click.stop="$emit('reject-friend', user.friendship_id)"
                                class="p-2 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-all"
                                title="Từ chối">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="18" y1="6" x2="6" y2="18" />
                                    <line x1="6" y1="6" x2="18" y2="18" />
                                </svg>
                            </button>
                        </template>

                        <!-- Sent request - Cancel -->
                        <template v-else-if="user.friendship_status === 'sent'">
                            <div class="p-2 text-purple-500" title="Đã gửi lời mời kết bạn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10" />
                                    <polyline points="12 6 12 12 16 14" />
                                </svg>
                            </div>
                            <button @click.stop="$emit('cancel-friend', user.friendship_id)"
                                class="p-2 rounded-lg text-gray-400 hover:text-orange-500 hover:bg-orange-50 transition-all"
                                title="Hủy lời mời">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="18" y1="6" x2="6" y2="18" />
                                    <line x1="6" y1="6" x2="18" y2="18" />
                                </svg>
                            </button>
                        </template>

                        <!-- No friendship - Add Friend -->
                        <button v-else-if="user.friendship_status === 'none' || !user.friendship_status" 
                            @click.stop="$emit('add-friend', user.id)"
                            class="p-2 rounded-lg text-gray-400 hover:text-blue-500 hover:bg-blue-50 transition-all"
                            :title="t('common.friend.add_friend')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                <circle cx="8.5" cy="7" r="4" />
                                <line x1="20" y1="8" x2="20" y2="14" />
                                <line x1="23" y1="11" x2="17" y2="11" />
                            </svg>
                        </button>

                        <!-- Blocked indicator -->
                        <div v-else-if="user.friendship_status === 'blocked'" 
                            class="p-2 text-gray-400" 
                            title="Đã chặn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="4.93" y1="4.93" x2="19.07" y2="19.07" />
                            </svg>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import type { IUser } from '../models/Chat'

const props = defineProps<{
    users: IUser[]
    loading: boolean
    searchQuery: string
}>()

defineEmits<{
    (e: 'start-chat', userId: number): void
    (e: 'add-friend', userId: number): void
    (e: 'accept-friend', friendshipId: number): void
    (e: 'reject-friend', friendshipId: number): void
    (e: 'cancel-friend', friendshipId: number): void
}>()

const { t } = useI18n()

const avatarColors = [
    'bg-teal-500', 'bg-blue-500', 'bg-purple-500', 'bg-pink-500',
    'bg-orange-500', 'bg-amber-500', 'bg-emerald-500', 'bg-cyan-500'
]

const groupedUsers = computed(() => {
    const groups: Record<string, IUser[]> = {}
    props.users.forEach(user => {
        const letter = user.name.charAt(0).toUpperCase()
        if (!groups[letter]) groups[letter] = []
        groups[letter].push(user)
    })
    return Object.keys(groups).sort().reduce((acc, key) => {
        acc[key] = groups[key]
        return acc
    }, {} as Record<string, IUser[]>)
})

function getAvatarColor(id: number): string {
    return avatarColors[id % avatarColors.length]
}

function getInitials(name: string): string {
    return name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase()
}
</script>
