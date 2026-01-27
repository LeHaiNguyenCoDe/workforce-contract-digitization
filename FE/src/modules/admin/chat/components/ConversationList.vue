<template>
    <div class="flex-1 overflow-y-auto">
        <!-- Loading -->
        <div v-if="loading" class="flex justify-center py-8">
            <div class="w-6 h-6 border-2 border-gray-200 border-t-teal-500 rounded-full animate-spin" />
        </div>

        <!-- Empty -->
        <div v-else-if="conversations.length === 0" class="flex flex-col items-center justify-center h-full p-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-gray-300 mb-3" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="1.5">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
            </svg>
            <p class="text-gray-400 text-sm">{{ t('common.chat.no_conversations') }}</p>
        </div>

        <!-- Conversations -->
        <div v-else class="p-2 space-y-0.5">
            <div v-for="conversation in conversations" :key="conversation.id" @click="$emit('select', conversation)"
                class="group flex items-center gap-3 px-3 py-2.5 cursor-pointer transition-all rounded-lg mx-1"
                :class="[selectedId === conversation.id ? 'bg-teal-50' : 'hover:bg-gray-50']">
                <!-- Avatar -->
                <div class="relative flex-shrink-0">
                    <img v-if="getAvatar(conversation)" :src="getAvatar(conversation) ?? undefined"
                        :alt="getName(conversation)" class="w-10 h-10 rounded-full object-cover" />
                    <div v-else
                        :class="['w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-medium', getAvatarColor(conversation.id)]">
                        {{ getInitials(getName(conversation)) }}
                    </div>
                    <!-- Online indicator -->
                    <div v-if="isOnline(conversation)"
                        class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white" />
                    <!-- Group indicator -->
                    <div v-if="conversation.type === 'group'"
                        class="absolute -bottom-0.5 -right-0.5 w-4 h-4 bg-blue-500 rounded-full border-2 border-white flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" viewBox="0 0 24 24" fill="none"
                            stroke="white" stroke-width="3">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg>
                    </div>
                </div>

                <!-- Info -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <h4
                            :class="['text-sm font-medium truncate', selectedId === conversation.id ? 'text-teal-700' : 'text-gray-700']">
                            {{ getName(conversation) }}
                        </h4>
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] text-gray-400 flex-shrink-0">
                                {{ formatTime(conversation.latest_message?.created_at) }}
                            </span>
                            <!-- Delete button (visible on hover) -->
                            <button @click.stop="$emit('delete', conversation.id)"
                                class="opacity-0 group-hover:opacity-100 p-1 rounded-md text-gray-400 hover:text-red-500 hover:bg-red-50 transition-all"
                                :title="t('common.chat.delete_chat')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6" />
                                    <path
                                        d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center justify-between mt-0.5">
                        <p class="text-xs text-gray-400 truncate pr-2">
                            {{ getPreview(conversation) }}
                        </p>
                        <span v-if="(conversation.unread_count || 0) > 0"
                            class="flex-shrink-0 min-w-[18px] h-[18px] px-1.5 rounded-full bg-teal-500 text-white text-[10px] font-medium flex items-center justify-center">
                            {{ conversation.unread_count }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import type { IConversation } from '../models/Chat'
import { useChatStore } from '../stores/chatStore'
import { useAuthStore } from '../../../../stores/auth'

defineProps<{
    conversations: IConversation[]
    selectedId?: number
    loading: boolean
}>()

defineEmits<{
    (e: 'select', conversation: IConversation): void
    (e: 'delete', id: number): void
}>()

const { t } = useI18n()
const chatStore = useChatStore()
const authStore = useAuthStore()

const currentUserId = computed(() => authStore.user?.id || 0)

const avatarColors = [
    'bg-teal-500', 'bg-blue-500', 'bg-purple-500', 'bg-pink-500',
    'bg-orange-500', 'bg-amber-500', 'bg-emerald-500', 'bg-cyan-500'
]

function getName(conversation: IConversation): string {
    if (conversation.name) return conversation.name
    
    // For private chats, find the partner (not me)
    if (conversation.type === 'private') {
        const partner = conversation.users.find(u => u.id !== currentUserId.value)
        if (partner) return partner.name
    }
    
    if (conversation.users.length === 1) return conversation.users[0].name
    return conversation.users.map(u => u.name.split(' ')[0]).join(', ')
}

function getAvatar(conversation: IConversation): string | null {
    if (conversation.avatar) return conversation.avatar
    if (conversation.type === 'private') {
        const partner = conversation.users.find(u => u.id !== currentUserId.value)
        return partner?.avatar || null
    }
    return null
}

function getAvatarColor(id: number): string {
    return avatarColors[id % avatarColors.length]
}

function getInitials(name: string): string {
    return name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase()
}

function isOnline(conversation: IConversation): boolean {
    if (conversation.type === 'private') {
        const partner = conversation.users.find(u => u.id !== currentUserId.value)
        return partner ? chatStore.isUserOnline(partner.id) : false
    }
    
    // For groups, check if any member (not me) is online
    return conversation.users.some(u => u.id !== currentUserId.value && chatStore.isUserOnline(u.id))
}

function getPreview(conversation: IConversation): string {
    const msg = conversation.latest_message
    if (!msg) return t('common.chat.no_messages')

    // Local check for is_mine, though backend should provide it
    const storedUserId = Number(localStorage.getItem('userId'))
    const isMine = msg.user_id == storedUserId
    const prefix = isMine ? `${t('common.chat.you')}: ` : ''

    if (msg.attachments && msg.attachments.length > 0) {
        const isImg = msg.attachments[0].file_type?.startsWith('image') ||
            /\.(jpg|jpeg|png|gif|webp|svg)$/i.test(msg.attachments[0].file_path || '')
        return prefix + (isImg ? t('common.chat.sent_image') : t('common.chat.sent_file'))
    }

    return prefix + (msg.content || '')
}

function formatTime(dateStr?: string): string {
    if (!dateStr) return ''
    const date = new Date(dateStr)
    const now = new Date()
    const diff = now.getTime() - date.getTime()

    if (diff < 86400000) {
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    }
    return date.toLocaleDateString([], { month: 'short', day: 'numeric' })
}
</script>
