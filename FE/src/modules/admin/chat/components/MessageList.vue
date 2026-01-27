<template>
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

                <MessageBubble
                    :message="msg"
                    :showAvatar="shouldShowAvatar(index)"
                    :currentUserId="currentUserId"
                    :theme="theme"
                    @reply="$emit('reply', $event)"
                    @delete="$emit('delete-message', $event)"
                />
            </template>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch, nextTick, onMounted, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import type { IMessage } from '../models/Chat'
import type { ChatTheme } from '../stores/chatThemeStore'
import MessageBubble from './MessageBubble.vue'

const props = defineProps<{
    messages: IMessage[]
    loading: boolean
    hasMore: boolean
    currentUserId: number
    theme?: ChatTheme | null
}>()

defineEmits<{
    (e: 'load-more'): void
    (e: 'reply', message: IMessage): void
    (e: 'delete-message', messageId: number): void
}>()

const { t } = useI18n()
const messagesContainer = ref<HTMLElement>()

/**
 * Check if date separator should be shown
 */
function shouldShowDate(index: number): boolean {
    if (index === 0) return true
    const curr = new Date(props.messages[index].created_at).toDateString()
    const prev = new Date(props.messages[index - 1].created_at).toDateString()
    return curr !== prev
}

/**
 * Check if avatar should be shown (last message from user or different sender next)
 */
function shouldShowAvatar(index: number): boolean {
    if (index === props.messages.length - 1) return true
    const curr = props.messages[index]
    const next = props.messages[index + 1]
    return curr.user_id !== next.user_id
}

/**
 * Format date for separator
 */
function formatDate(dateStr: string): string {
    const date = new Date(dateStr)
    const today = new Date()
    const yesterday = new Date(today)
    yesterday.setDate(yesterday.getDate() - 1)

    if (date.toDateString() === today.toDateString()) return t('common.chat.today')
    if (date.toDateString() === yesterday.toDateString()) return t('common.chat.yesterday')
    return date.toLocaleDateString([], { month: 'long', day: 'numeric', year: 'numeric' })
}

/**
 * Scroll to bottom of messages
 */
const scrollToBottom = (behavior: ScrollBehavior = 'smooth') => {
    if (messagesContainer.value) {
        messagesContainer.value.scrollTo({
            top: messagesContainer.value.scrollHeight,
            behavior
        })
    }
}

/**
 * Check if user is near bottom of scroll
 */
const isAtBottom = () => {
    if (!messagesContainer.value) return true
    const threshold = 150
    const position = messagesContainer.value.scrollTop + messagesContainer.value.clientHeight
    const height = messagesContainer.value.scrollHeight
    return height - position < threshold
}

// Listen for new messages from store
let messageArrivalHandler: EventListener | null = null

onMounted(() => {
    messageArrivalHandler = ((e: CustomEvent) => {
        const { isFromSelf } = e.detail

        // Always scroll if it's our own message or if we are already at the bottom
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

// Watch for initial load
watch(() => props.messages.length, (newVal, oldVal) => {
    // If it's the first load (from 0 to N), scroll to bottom
    if ((oldVal === 0 || oldVal === undefined) && newVal > 0) {
        nextTick(() => {
            scrollToBottom('auto')
        })
    }
})

// Expose methods for parent component if needed
defineExpose({
    scrollToBottom,
    isAtBottom
})
</script>
