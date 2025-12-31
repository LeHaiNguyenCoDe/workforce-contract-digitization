<template>
    <div class="flex flex-col h-full bg-[#f0f4f3]"
        style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%2326a69a&quot; fill-opacity=&quot;0.03&quot;%3E%3Ccircle cx=&quot;30&quot; cy=&quot;30&quot; r=&quot;4&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');">
        <!-- Header -->
        <header class="flex items-center justify-between px-4 py-3 bg-white border-b border-gray-100 flex-shrink-0">
            <div class="flex items-center gap-3 min-w-0">
                <!-- Back button on mobile -->
                <button @click="$emit('back')"
                    class="md:hidden w-8 h-8 -ml-1 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <polyline points="15 18 9 12 15 6" />
                    </svg>
                </button>
                <div class="relative flex-shrink-0">
                    <img v-if="getAvatar()" :src="getAvatar() || undefined" :alt="getName()"
                        class="w-10 h-10 rounded-full object-cover" />
                    <div v-else
                        :class="['w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-medium', getAvatarColor()]">
                        {{ getInitials(getName()) }}
                    </div>
                    <div v-if="isUserOnline"
                        class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white" />
                </div>
                <div class="min-w-0">
                    <h3 class="text-sm font-semibold text-gray-800 truncate">{{ getName() }}</h3>
                    <p v-if="typingUsers.length > 0" class="text-xs text-teal-500 animate-pulse">
                        {{ getTypingText() }}
                    </p>
                    <p v-else class="text-xs text-gray-400">
                        {{ isGuestChat ? 'Khách hàng' : (isUserOnline ? t('common.chat.online') : (conversation.type ===
                            'group' ?
                            `${conversation.users.length} ${t('common.chat.members')}` : t('common.chat.offline'))) }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-1">
                <!-- Header Action Icons -->
                <button
                    class="w-9 h-9 rounded-lg text-gray-400 hover:text-teal-600 hover:bg-teal-50 flex items-center justify-center transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path
                            d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                    </svg>
                </button>

                <button
                    class="w-9 h-9 rounded-lg text-gray-400 hover:text-teal-600 hover:bg-teal-50 flex items-center justify-center transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="m22 8-6 4 6 4V8Z" />
                        <rect width="14" height="12" x="2" y="6" rx="2" ry="2" />
                    </svg>
                </button>

                <button @click="$emit('toggle-details')"
                    :class="['w-9 h-9 rounded-lg flex items-center justify-center transition-all', showDetails ? 'text-teal-600 bg-teal-50 hover:bg-teal-100' : 'text-gray-400 hover:text-teal-600 hover:bg-teal-50']">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="16" x2="12" y2="12" />
                        <line x1="12" y1="8" x2="12.01" y2="8" />
                    </svg>
                </button>

                <!-- Assign Staff Button (System Admins only) -->
                <button v-if="isGuestChat && canAssign" @click="showAssignModal = true"
                    class="ml-2 flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-amber-700 bg-amber-50 hover:bg-amber-100 rounded-full transition-all border border-amber-100"
                    title="Phân công nhân viên trả lời">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <polyline points="16 11 18 13 22 9"></polyline>
                    </svg>
                    <span>Phân công</span>
                </button>

                <!-- More Actions Menu -->
                <div class="relative group">
                    <button
                        class="w-9 h-9 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 flex items-center justify-center transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="1" />
                            <circle cx="19" cy="12" r="1" />
                            <circle cx="5" cy="12" r="1" />
                        </svg>
                    </button>

                    <!-- Dropdown -->
                    <div
                        class="absolute right-0 top-full mt-1 w-48 bg-white rounded-xl shadow-xl border border-gray-100 py-1 z-50 hidden group-hover:block">
                        <button v-if="conversation.type === 'group'" @click="handleLeaveGroup"
                            class="w-full px-4 py-2 text-left text-sm text-amber-600 hover:bg-amber-50 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                <polyline points="16 17 21 12 16 7" />
                                <line x1="21" y1="12" x2="9" y2="12" />
                            </svg>
                            {{ t('common.chat.leave_group') }}
                        </button>
                        <button @click="handleDeleteConversation"
                            class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6" />
                                <path
                                    d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                            </svg>
                            {{ conversation.type === 'group' ? t('common.chat.delete_group') :
                                t('common.chat.delete_chat') }}
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Messages -->
        <div ref="messagesContainer" class="flex-1 overflow-y-auto px-4 py-4">
            <!-- Load More -->
            <div v-if="hasMore" class="flex justify-center mb-4">
                <button @click="$emit('load-more')" :disabled="loading"
                    class="px-4 py-1.5 text-xs text-gray-400 hover:text-gray-600 bg-white hover:bg-gray-50 rounded-full shadow-sm border border-gray-100 transition-all disabled:opacity-50">
                    {{ loading ? '...' : t('common.chat.load_more') }}
                </button>
            </div>

            <!-- Loading -->
            <div v-if="loading && messages.length === 0" class="flex justify-center py-8">
                <div class="w-6 h-6 border-2 border-gray-200 border-t-teal-500 rounded-full animate-spin" />
            </div>

            <!-- Messages -->
            <div class="flex flex-col space-y-4">
                <template v-for="(msg, index) in messages" :key="msg.id">
                    <!-- Date separator -->
                    <div v-if="shouldShowDate(index)" class="flex items-center justify-center my-4">
                        <span class="px-3 py-1 text-xs text-gray-500 bg-white rounded-full shadow-sm">
                            {{ formatDate(msg.created_at) }}
                        </span>
                    </div>

                    <MessageBubble :message="msg" :showAvatar="shouldShowAvatar(index)" :currentUserId="currentUserId"
                        @reply="handleReply" @delete="handleDeleteMessage" />
                </template>
            </div>
        </div>

        <!-- Input -->
        <MessageInput :disabled="sending" :replyTo="replyToMessage" @send="handleSend" @typing="$emit('typing', $event)"
            @cancel-reply="replyToMessage = null" />

        <!-- Staff Assignment Modal -->
        <StaffAssignmentModal v-if="showAssignModal" @close="showAssignModal = false" @select="onStaffSelected" />
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, nextTick } from 'vue'
import { useI18n } from 'vue-i18n'
import type { IConversation, IMessage } from '../models/Chat'
import MessageBubble from './MessageBubble.vue'
import MessageInput from './MessageInput.vue'
import StaffAssignmentModal from './StaffAssignmentModal.vue'

import { useAuthStore } from '@/stores/auth'
import { useChatStore } from '../stores/chatStore'

const props = defineProps<{
    conversation: IConversation
    messages: IMessage[]
    loading: boolean
    sending: boolean
    hasMore: boolean
    typingUsers: string[]
    showDetails?: boolean
}>()

const emit = defineEmits<{
    (e: 'send', content: string, attachments?: File[], replyToId?: number): void
    (e: 'load-more'): void
    (e: 'typing', isTyping: boolean): void
    (e: 'back'): void
    (e: 'delete-message', messageId: number): void
    (e: 'delete-conversation', conversationId: number): void
    (e: 'leave-group', conversationId: number): void
    (e: 'toggle-details'): void
}>()

const { t } = useI18n()
const authStore = useAuthStore()
const chatStore = useChatStore()

const avatarColors = ['bg-teal-500', 'bg-blue-500', 'bg-purple-500', 'bg-pink-500', 'bg-orange-500']

const messagesContainer = ref<HTMLElement>()
const replyToMessage = ref<IMessage | null>(null)

const currentUserId = computed(() => {
    return authStore.user?.id || 0
})

const isUserOnline = computed(() => {
    if (props.conversation.type === 'private' && props.conversation.users?.length === 1) {
        return props.conversation.users[0].is_online || false
    }
    return false
})

const isGuestChat = computed(() => {
    return !!props.conversation.is_guest
})

const canAssign = computed(() => {
    // Only admins or managers for now - assuming role is in authStore.user
    const role = authStore.user?.role
    return role === 'admin' || role === 'manager' || authStore.user?.email?.includes('admin') || authStore.user?.email?.includes('manager')
})

const showAssignModal = ref(false)

async function onStaffSelected(userId: number) {
    if (!isGuestChat.value) return

    if (!props.conversation.guest_session) {
        console.error('ChatWindow: Missing guest_session for assignment', props.conversation)
        return
    }

    try {
        await chatStore.assignGuestChat(props.conversation.guest_session.session_token, userId)
        showAssignModal.value = false
        // Trigger a refresh if needed
        chatStore.fetchConversations(1)
    } catch (error) {
        console.error('Failed to assign staff:', error)
    }
}

function getName(): string {
    if (props.conversation.name) return props.conversation.name
    if (!props.conversation.users || props.conversation.users.length === 0) return t('common.chat.no_name')
    if (props.conversation.users.length === 1) return props.conversation.users[0].name
    return props.conversation.users.map(u => u.name.split(' ')[0]).join(', ')
}

function getAvatar(): string | null {
    if (props.conversation.avatar) return props.conversation.avatar
    if (props.conversation.type === 'private' && props.conversation.users?.length === 1) {
        return props.conversation.users[0].avatar || null
    }
    return null
}

function getAvatarColor(): string {
    return avatarColors[props.conversation.id % avatarColors.length]
}

function getInitials(name: string): string {
    return name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase()
}

function getTypingText(): string {
    if (props.typingUsers.length === 1) return `${props.typingUsers[0]} ${t('common.chat.is_typing')}`
    if (props.typingUsers.length === 2) return `${props.typingUsers.join(' & ')} ${t('common.chat.are_typing')}`
    return t('common.chat.several_typing')
}

function shouldShowDate(index: number): boolean {
    if (index === 0) return true
    const curr = new Date(props.messages[index].created_at).toDateString()
    const prev = new Date(props.messages[index - 1].created_at).toDateString()
    return curr !== prev
}

function shouldShowAvatar(index: number): boolean {
    if (index === props.messages.length - 1) return true
    const curr = props.messages[index]
    const next = props.messages[index + 1]
    return curr.user_id !== next.user_id
}

function formatDate(dateStr: string): string {
    const date = new Date(dateStr)
    const today = new Date()
    const yesterday = new Date(today)
    yesterday.setDate(yesterday.getDate() - 1)

    if (date.toDateString() === today.toDateString()) return t('common.chat.today')
    if (date.toDateString() === yesterday.toDateString()) return t('common.chat.yesterday')
    return date.toLocaleDateString([], { month: 'long', day: 'numeric', year: 'numeric' })
}

function handleReply(message: IMessage) {
    replyToMessage.value = message
}

function handleDeleteMessage(messageId: number) {
    if (confirm(t('common.chat.confirm_delete_message'))) {
        emit('delete-message', messageId)
    }
}

function handleDeleteConversation() {
    const message = props.conversation.type === 'group'
        ? t('common.chat.confirm_delete_group')
        : t('common.chat.confirm_delete_chat')

    if (confirm(message)) {
        emit('delete-conversation', props.conversation.id)
    }
}

function handleLeaveGroup() {
    if (confirm(t('common.chat.confirm_leave_group'))) {
        emit('leave-group', props.conversation.id)
    }
}

function handleSend(content: string, attachments?: File[]) {
    emit('send', content, attachments, replyToMessage.value?.id)
    replyToMessage.value = null
}

// Smart scroll to bottom
const scrollToBottom = (behavior: ScrollBehavior = 'smooth') => {
    if (messagesContainer.value) {
        messagesContainer.value.scrollTo({
            top: messagesContainer.value.scrollHeight,
            behavior
        })
    }
}

// Check if user is near bottom
const isAtBottom = () => {
    if (!messagesContainer.value) return true
    const threshold = 150 // px
    const position = messagesContainer.value.scrollTop + messagesContainer.value.clientHeight
    const height = messagesContainer.value.scrollHeight
    return height - position < threshold
}

// Listen for new messages from store
let messageArrivalHandler: EventListener | null = null

onMounted(() => {
    messageArrivalHandler = ((e: CustomEvent) => {
        const { isFromSelf } = e.detail

        // Always scroll if it's our own message
        // Or if we are already at the bottom
        if (isFromSelf || isAtBottom()) {
            nextTick(() => {
                scrollToBottom(isFromSelf ? 'auto' : 'smooth')
            })
        }
    }) as EventListener

    window.addEventListener('chat:new-message-arrived', messageArrivalHandler)

    // Initial scroll
    nextTick(() => {
        scrollToBottom('auto')
    })
})

onUnmounted(() => {
    if (messageArrivalHandler) {
        window.removeEventListener('chat:new-message-arrived', messageArrivalHandler)
    }
})

// Still watch for count changes but only for initial load or manual triggers if needed
watch(() => props.messages.length, (newVal, oldVal) => {
    // If it's the first load (from 0 to N), scroll to bottom
    if ((oldVal === 0 || oldVal === undefined) && newVal > 0) {
        nextTick(() => {
            scrollToBottom('auto')
        })
    }
})
</script>
