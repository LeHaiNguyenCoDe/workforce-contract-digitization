<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed, watch } from 'vue'
import { useNotificationStore } from '@/modules/admin/chat/stores/notificationStore'
import { useChatStore } from '@/modules/admin/chat/stores/chatStore'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import type { INotification } from '@/modules/admin/chat/models/Chat'

const { t } = useI18n()
const router = useRouter()
const notificationStore = useNotificationStore()
const chatStore = useChatStore()

const isDropdownOpen = ref(false)
const showToast = ref(false)
const latestNotification = ref<any>(null)
let toastTimer: ReturnType<typeof setTimeout> | null = null

const unreadCount = computed(() => (notificationStore.unreadCount || 0) + (chatStore.totalUnreadCount || 0))

// Combined notifications list
const combinedNotifications = computed(() => {
    // 1. System Notifications (Both read and unread)
    const systemNotifs = notificationStore.notifications.map(n => ({
        ...n,
        is_system: true,
        sort_date: n.created_at
    }))

    // 2. Chat Notifications
    // Show all recent conversations, but prioritize unread ones in sorting
    // We take the top 10 most recent active conversations to keep the list clean
    const chatNotifs = chatStore.sortedConversations.slice(0, 10).map(c => ({
        id: `chat_${c.id}`,
        type: 'message',
        is_system: false,
        data: {
            content: c.latest_message?.content || (c.latest_message?.attachments?.length ? t('common.chat.sent_file') : t('common.chat.new_message', { name: c.name })),
            conversation_id: c.id
        },
        read_at: (c.unread_count || 0) === 0 ? new Date().toISOString() : null, // If unread_count is 0, consider it read
        created_at: c.latest_message?.created_at || c.updated_at,
        sort_date: c.latest_message?.created_at || c.updated_at,
        sender_name: c.name || c.users.find((u: any) => u.id !== (window as any).authStore?.user?.id)?.name
    }))

    return [...chatNotifs, ...systemNotifs].sort((a, b) =>
        new Date(b.sort_date).getTime() - new Date(a.sort_date).getTime()
    )
})

// Watch for new system notifications to show toast
watch(() => notificationStore.notifications.length, (newLength, oldLength) => {
    if (newLength > oldLength && notificationStore.notifications.length > 0) {
        const newNotif = notificationStore.notifications[0]
        if (!newNotif.read_at) {
            handleNewNotification(newNotif)
        }
    }
})

let unreadPollingInterval: ReturnType<typeof setInterval> | null = null

// Listen for global toast events (e.g. for messages)
const toastHandler = ((e: CustomEvent) => {
    handleNewNotification(e.detail)
}) as EventListener

onMounted(async () => {
    try {
        await Promise.all([
            notificationStore.fetchUnreadCount(),
            notificationStore.fetchNotifications(1)
        ])
    } catch (error) {
        console.error('NotificationBell: Failed to initial fetch', error)
    }

    window.addEventListener('notification:show-toast', toastHandler)

    // Fallback polling for unread count every 30 seconds
    unreadPollingInterval = setInterval(() => {
        notificationStore.fetchUnreadCount()
    }, 30000)
})

// Cleanup on unmount - Moved outside onMounted to fix lifecycle warning
onUnmounted(() => {
    window.removeEventListener('notification:show-toast', toastHandler)
    if (unreadPollingInterval) clearInterval(unreadPollingInterval)
})

const toggleDropdown = () => {
    isDropdownOpen.value = !isDropdownOpen.value
}

const closeDropdown = () => {
    isDropdownOpen.value = false
}

const markAsRead = async (notification: any) => {
    if (notification.is_system && !notification.read_at) {
        await notificationStore.markAsRead(notification.id)
    }
}

const markAllAsRead = async () => {
    await notificationStore.markAllAsRead()
    // For chats, we can't easily mark all as read without opening them
    // so we might just leave them as unread or implement a mark all read for chats later
}

const handleNewNotification = (notification: any) => {
    console.log('NotificationBell: Handling new notification', notification)
    latestNotification.value = notification
    showToast.value = true

    if (toastTimer) {
        clearTimeout(toastTimer)
    }

    toastTimer = setTimeout(() => {
        showToast.value = false
        console.log('NotificationBell: Hiding toast')
    }, 5000)
}

const handleNotificationClick = async (item: any) => {
    // If it's a system notification with an ID string, mark it as read
    if (item.is_system && item.id && typeof item.id === 'string' && !item.read_at) {
        await markAsRead(item)
    }

    closeDropdown()
    showToast.value = false

    // Navigation logic
    if (item.type === 'message' || item.conversation_id || item.data?.conversation_id) {
        const conversationId = item.conversation_id || item.data?.conversation_id
        const messageId = item.message_id || item.data?.message_id

        // Use path instead of route name
        router.push('/admin/chat')

        if (conversationId) {
            // Delay slightly to ensure ChatView is mounted
            setTimeout(() => {
                window.dispatchEvent(new CustomEvent('chat:select-and-scroll', {
                    detail: { conversationId, messageId }
                }))
            }, 200)
        }
    } else if (item.data?.url) {
        window.location.href = item.data.url
    }
}

const formatData = (item: any) => {
    if (!item) return ''
    const data = item.data
    const type = item.type

    // Handle System Notifications
    if (item.is_system) {
        if (type === 'friend_request') {
            return t('common.notifications.friend_request', { name: data?.from_user_name || t('common.chat.unknown_user') })
        }
        if (type === 'friend_accepted') {
            return t('common.notifications.friend_accepted', { name: data?.user_name || t('common.chat.unknown_user') })
        }
        if (type === 'group_invite') {
            return t('common.notifications.group_invite', { name: data?.from_user_name || t('common.chat.unknown_user'), group: data?.group_name || 'Group' })
        }
    }

    // Handle Chat Messages
    if (type === 'message') {
        if (typeof data === 'string') return data
        if (data?.content) return data.content
        if (data?.message) return data.message
        return t('common.chat.new_message', { name: item.sender_name || t('common.chat.unknown_user') })
    }

    // Generic fallbacks
    if (typeof data === 'string') return data
    if (data?.content) return data.content
    if (data?.message) return data.message

    return t('common.notifications.generic')
}

const getTimeAgo = (dateString: string) => {
    if (!dateString) return ''
    const date = new Date(dateString)
    const now = new Date()
    const diffInSeconds = Math.floor((now.getTime() - date.getTime()) / 1000)

    if (diffInSeconds < 60) return t('common.chat.time.just_now')
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m`
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h`
    return `${Math.floor(diffInSeconds / 86400)}d`
}

defineExpose({ handleNewNotification })
</script>

<template>
    <div class="relative">
        <!-- Bell Icon -->
        <button @click.stop="toggleDropdown"
            class="relative p-2 text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2">
                <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
                <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
            </svg>
            <span v-if="unreadCount > 0"
                class="absolute top-1.5 right-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-error text-[10px] font-bold text-white ring-2 ring-dark-800">
                {{ unreadCount > 9 ? '9+' : unreadCount }}
            </span>
        </button>

        <!-- Dropdown (Swagger Panel) -->
        <div v-if="isDropdownOpen" v-click-outside="closeDropdown"
            class="absolute right-0 mt-2 w-80 bg-dark-800 border border-white/10 rounded-xl shadow-2xl z-[60] overflow-hidden">
            <div class="flex items-center justify-between p-4 border-b border-white/10">
                <h3 class="text-sm font-bold text-white">{{ t('admin.notifications') }}</h3>
                <button v-if="notificationStore.unreadCount > 0" @click="markAllAsRead"
                    class="text-xs text-primary hover:text-primary-light transition-colors">
                    {{ t('common.mark_all_read') }}
                </button>
            </div>

            <div class="max-h-[400px] overflow-y-auto">
                <div v-if="combinedNotifications.length === 0" class="p-8 text-center">
                    <p class="text-sm text-slate-500">{{ t('common.no_data') }}</p>
                </div>
                <div v-else v-for="item in combinedNotifications" :key="item.id" @click="handleNotificationClick(item)"
                    class="p-4 border-b border-white/5 hover:bg-white/5 transition-colors cursor-pointer group"
                    :class="{ 'bg-primary/5': !item.read_at }">
                    <div class="flex gap-3">
                        <div
                            class="w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center text-primary flex-shrink-0">
                            <svg v-if="item.type === 'message'" xmlns="http://www.w3.org/2000/svg" width="14"
                                height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                            </svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                                <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-white leading-relaxed" :class="{ 'font-bold': !item.read_at }">
                                {{ formatData(item) }}
                            </p>
                            <span class="text-[10px] text-slate-500 mt-1 block">{{ getTimeAgo(item.created_at) }}</span>
                        </div>
                        <div v-if="!item.read_at" class="w-2 h-2 rounded-full bg-primary mt-1.5 flex-shrink-0"></div>
                    </div>
                </div>
            </div>

            <div class="p-3 text-center border-t border-white/10 bg-white/5">
                <button class="text-xs text-slate-400 hover:text-white transition-colors">
                    {{ t('common.view_all') }}
                </button>
            </div>
        </div>

        <!-- Realtime Toast (Swagger Popup) -->
        <Transition enter-active-class="transform transition ease-out duration-300"
            enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100"
            leave-to-class="opacity-0">
            <div v-if="showToast && latestNotification" @click="handleNotificationClick(latestNotification)"
                class="fixed top-24 right-6 w-96 bg-dark-800 border-l-4 border-primary shadow-2xl p-4 rounded-r-xl z-[10000] flex gap-4 animate-slide-in cursor-pointer hover:bg-dark-700 transition-colors">
                <div
                    class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center text-primary flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                        <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="text-sm font-bold text-white mb-1">{{ t('admin.new_notification') }}</h4>
                    <p class="text-xs text-slate-300 line-clamp-2">
                        {{ formatData(latestNotification) }}
                    </p>
                </div>
                <button @click.stop="showToast = false" class="text-slate-500 hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="M18 6 6 18M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </Transition>
    </div>
</template>

