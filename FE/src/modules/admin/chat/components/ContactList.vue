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

                    <!-- Actions -->
                    <div class="flex items-center gap-1.5 flex-shrink-0">
                        <!-- Start Chat -->
                        <button @click.stop="$emit('start-chat', user.id)"
                            class="p-2 rounded-lg text-gray-400 hover:text-teal-500 hover:bg-teal-50 transition-all"
                            :title="t('common.chat.start_chat')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                            </svg>
                        </button>

                        <!-- Add Friend (if not already friend) -->
                        <button v-if="!isFriend(user.id)" @click.stop="$emit('add-friend', user.id)"
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

                        <!-- Already Friend indicator -->
                        <div v-else class="p-2 text-green-500" :title="t('common.messages.friend.already_friends')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                <circle cx="8.5" cy="7" r="4" />
                                <polyline points="17 11 19 13 23 9" />
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
    friends: number[]
    loading: boolean
    searchQuery: string
}>()

defineEmits<{
    (e: 'start-chat', userId: number): void
    (e: 'add-friend', userId: number): void
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

function isFriend(userId: number): boolean {
    return props.friends.includes(userId)
}
</script>
