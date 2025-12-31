<template>
    <div class="h-full flex flex-col bg-gray-50">
        <div class="flex flex-1 overflow-hidden bg-white shadow-sm border border-gray-100">
            <!-- Sidebar -->
            <aside class="absolute md:relative inset-y-0 left-0 z-20 
                       w-full sm:w-72 md:w-64 lg:w-72 
                       flex flex-col bg-white border-r border-gray-100
                       transform transition-transform duration-300
                       md:translate-x-0" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'">
                <!-- Header -->
                <div class="flex justify-between items-center p-4 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-800">{{ t('common.chat.title') }}</h2>
                    <div class="flex items-center gap-2">
                        <button @click="showNewChat = true"
                            class="w-8 h-8 rounded-lg bg-teal-500 hover:bg-teal-600 text-white flex items-center justify-center transition-all shadow-sm"
                            :title="t('common.chat.new_chat')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 5v14M5 12h14" />
                            </svg>
                        </button>
                        <button @click="sidebarOpen = false"
                            class="md:hidden w-8 h-8 rounded-lg bg-gray-100 text-gray-500 hover:text-gray-700 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18" />
                                <line x1="6" y1="6" x2="18" y2="18" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Search -->
                <div class="p-3">
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8" />
                            <path d="m21 21-4.35-4.35" />
                        </svg>
                        <input v-model="searchQuery" type="text" :placeholder="t('common.chat.search_placeholder')"
                            class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-100 transition-all" />
                    </div>
                </div>

                <!-- Tabs -->
                <div class="flex px-3 pb-2 gap-1">
                    <button :class="[
                        'flex-1 py-2 text-xs font-medium rounded-lg transition-all',
                        activeListTab === 'contacts'
                            ? 'bg-teal-50 text-teal-600'
                            : 'text-gray-500 hover:bg-gray-50'
                    ]" @click="activeListTab = 'contacts'">
                        Contacts
                    </button>
                    <button :class="[
                        'flex-1 py-2 text-xs font-medium rounded-lg transition-all',
                        activeListTab === 'chats'
                            ? 'bg-teal-50 text-teal-600'
                            : 'text-gray-500 hover:bg-gray-50'
                    ]" @click="activeListTab = 'chats'">
                        Chats
                    </button>
                    <button :class="[
                        'flex-1 py-2 text-xs font-medium rounded-lg transition-all',
                        activeListTab === 'groups'
                            ? 'bg-teal-50 text-teal-600'
                            : 'text-gray-500 hover:bg-gray-50'
                    ]" @click="activeListTab = 'groups'">
                        Groups
                    </button>
                    <!-- Guest Tab - only show if user has guest chats -->
                    <button v-if="hasGuestChats" :class="[
                        'flex-1 py-2 text-xs font-medium rounded-lg transition-all relative',
                        activeListTab === 'guests'
                            ? 'bg-amber-50 text-amber-600'
                            : 'text-gray-500 hover:bg-gray-50'
                    ]" @click="activeListTab = 'guests'">
                        <span class="flex items-center justify-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                            Guest
                        </span>
                    </button>
                </div>

                <!-- Content based on tab -->
                <!-- Contacts Tab -->
                <ContactList v-if="activeListTab === 'contacts'" :users="filteredUsers" :loading="isLoadingUsers"
                    :searchQuery="searchQuery" @start-chat="handleStartChatFromContact" @add-friend="handleAddFriend"
                    @accept-friend="handleAcceptFriend" @reject-friend="handleRejectFriend"
                    @cancel-friend="handleCancelFriend" />

                <!-- Chats Tab -->
                <ConversationList v-if="activeListTab === 'chats'" :conversations="filteredPrivateConversations"
                    :selectedId="currentConversation?.id" :loading="isLoadingConversations"
                    @select="handleSelectConversation" @delete="handleDeleteConversation" />

                <!-- Groups Tab -->
                <ConversationList v-if="activeListTab === 'groups'" :conversations="filteredGroupConversations"
                    :selectedId="currentConversation?.id" :loading="isLoadingConversations"
                    @select="handleSelectConversation" @delete="handleDeleteConversation" />

                <!-- Guest Tab -->
                <ConversationList v-if="activeListTab === 'guests'" :conversations="filteredGuestConversations"
                    :selectedId="currentConversation?.id" :loading="isLoadingGuestConversations"
                    @select="handleSelectConversation" @delete="handleDeleteConversation" />
            </aside>

            <!-- Backdrop for mobile -->
            <div v-if="sidebarOpen" @click="sidebarOpen = false" class="md:hidden fixed inset-0 z-10 bg-black/20" />

            <!-- Main Chat Area -->
            <main class="flex-1 flex flex-col overflow-hidden min-w-0 bg-[#f0f4f3]"
                style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%2326a69a&quot; fill-opacity=&quot;0.03&quot;%3E%3Ccircle cx=&quot;30&quot; cy=&quot;30&quot; r=&quot;4&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');">
                <!-- Mobile menu button -->
                <button v-if="!currentConversation" @click="sidebarOpen = true"
                    class="md:hidden absolute top-4 left-4 z-30 p-2 rounded-lg bg-white text-gray-600 shadow-md">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <line x1="3" y1="12" x2="21" y2="12" />
                        <line x1="3" y1="6" x2="21" y2="6" />
                        <line x1="3" y1="18" x2="21" y2="18" />
                    </svg>
                </button>

                <template v-if="currentConversation">
                    <ChatWindow :conversation="currentConversation" :messages="messages" :loading="isLoadingMessages"
                        :sending="isSendingMessage" :hasMore="hasMoreMessages"
                        :typingUsers="Array.from(typingUsers.values()).map(u => u.userName)" @send="handleSendMessage"
                        @load-more="handleLoadMore" @typing="handleTyping" @back="handleBack"
                        @delete-message="handleDeleteMessage" @delete-conversation="handleDeleteConversation"
                        @leave-group="handleLeaveGroup" />
                </template>
                <template v-else>
                    <div class="flex-1 flex items-center justify-center p-4">
                        <div class="text-center">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-16 h-16 md:w-20 md:h-20 mx-auto mb-4 text-teal-200" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                            </svg>
                            <h3 class="text-xl font-medium text-gray-400 mb-2">{{ t('common.chat.select_conversation')
                            }}</h3>
                            <p class="text-gray-400 max-w-xs mx-auto">{{ t('common.chat.select_hint') }}</p>
                        </div>
                    </div>
                </template>
            </main>
        </div>

        <!-- New Chat Modal -->
        <NewChatModal v-if="showNewChat" @close="showNewChat = false" @start-chat="handleStartChat"
            @create-group="handleCreateGroup" />
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useChatStore } from '../stores/chatStore'
import { useAuthStore } from '@/stores/auth'
import { storeToRefs } from 'pinia'
import { useRealtime } from '../composables/useRealtime'
import { FriendService } from '../services/FriendService'
import { ChatService } from '../services/ChatService'
import { NotificationHelper } from '../helpers/notificationHelper'
import ContactList from '../components/ContactList.vue'
import ConversationList from '../components/ConversationList.vue'
import ChatWindow from '../components/ChatWindow.vue'
import NewChatModal from '../components/NewChatModal.vue'
import type { IConversation, IUser } from '../models/Chat'

const { t } = useI18n()
const chatStore = useChatStore()
const {
    connect,
    disconnect,
    subscribeToConversation,
    unsubscribeFromConversation,
    sendTypingIndicator
} = useRealtime()

const {
    currentConversation,
    messages,
    isLoadingConversations,
    isLoadingMessages,
    isSendingMessage,
    hasMoreMessages,
    typingUsers,
    sortedConversations
} = storeToRefs(chatStore)

const searchQuery = ref('')
const showNewChat = ref(false)
const sidebarOpen = ref(true)
const activeListTab = ref<'contacts' | 'chats' | 'groups' | 'guests'>('chats')

// Users data for Contacts tab
const allUsers = ref<IUser[]>([])
const isLoadingUsers = ref(false)

// Filtered data
const filteredUsers = computed(() => {
    if (!searchQuery.value) return allUsers.value
    const query = searchQuery.value.toLowerCase()
    return allUsers.value.filter(user =>
        user.name.toLowerCase().includes(query) ||
        (user.email && user.email.toLowerCase().includes(query))
    )
})

const filteredPrivateConversations = computed(() => {
    // Only show internal private chats, exclude guest conversations
    const list = sortedConversations.value.filter(c => {
        const isPrivate = c.type === 'private'
        const isGuest = c.is_guest === true || !!c.guest_session
        return isPrivate && !isGuest
    })

    if (!searchQuery.value) return list
    const query = searchQuery.value.toLowerCase()
    return list.filter(conv => {
        if (conv.name) return conv.name.toLowerCase().includes(query)
        return conv.users.some(u => u.name.toLowerCase().includes(query))
    })
})

const filteredGroupConversations = computed(() => {
    const list = sortedConversations.value.filter(c => c.type === 'group')
    if (!searchQuery.value) return list
    const query = searchQuery.value.toLowerCase()
    return list.filter(conv => conv.name?.toLowerCase().includes(query))
})

const isLoadingGuestConversations = ref(false)

// Show Guest tab for admin/manager OR if user has assigned guest chats
const authStore = useAuthStore()
const hasGuestChats = computed(() => {
    const user = authStore.user
    // Check if store already has guest conversations
    const storeHasGuest = sortedConversations.value.some(c => c.is_guest || !!c.guest_session)
    if (storeHasGuest) return true

    if (!user) return false

    // Check role string
    const adminRoles = ['admin', 'manager', 'super_admin']
    if (user.role && adminRoles.includes(user.role)) {
        return true
    }

    // Check roles array (Laravel format)
    if (user.roles && Array.isArray(user.roles)) {
        const hasAdminRole = user.roles.some((r: any) => adminRoles.includes(r.name || r))
        if (hasAdminRole) return true
    }

    return false
})

const filteredGuestConversations = computed(() => {
    const list = sortedConversations.value.filter(c => c.is_guest || !!c.guest_session)
    if (!searchQuery.value) return list
    const query = searchQuery.value.toLowerCase()
    return list.filter(conv =>
        conv.name?.toLowerCase().includes(query) ||
        conv.guest_session?.guest_name?.toLowerCase().includes(query)
    )
})

async function loadGuestConversations() {
    // We already fetch everything in onMounted via chatStore.fetchConversations()
    // but if we want to be sure to get all guest sessions (even unassigned ones for admins),
    // we can keep a dedicated fetch if the backend returns different results for 'guest' type.
    isLoadingGuestConversations.value = true
    try {
        const response = await ChatService.getConversations(1, 50, 'guest')
        // Merge into store if not already there
        if (response.data) {
            response.data.forEach(conv => {
                if (!chatStore.conversations.some(c => c.id === conv.id)) {
                    chatStore.conversations.push(conv)
                }
            })
        }
    } catch (error) {
        console.error('Failed to load guest conversations:', error)
    } finally {
        isLoadingGuestConversations.value = false
    }
}

// Load users for Contacts tab (with friendship status from backend)
async function loadUsers() {
    isLoadingUsers.value = true
    try {
        allUsers.value = await FriendService.getAllUsers()
    } catch (error) {
        console.error('Failed to load users:', error)
    } finally {
        isLoadingUsers.value = false
    }
}

function handleSelectConversation(conversation: IConversation) {
    if (currentConversation.value) {
        unsubscribeFromConversation(currentConversation.value.id)
    }
    chatStore.selectConversation(conversation)
    subscribeToConversation(conversation.id)
    sidebarOpen.value = false
}

function handleBack() {
    chatStore.selectConversation(null as any)
    sidebarOpen.value = true
}

async function handleStartChatFromContact(userId: number) {
    try {
        const conversation = await chatStore.startPrivateChat(userId)

        subscribeToConversation(conversation.id)
        activeListTab.value = 'chats'
        sidebarOpen.value = false

        // Ensure conversations are refreshed if it was a brand new one
        if (!sortedConversations.value.some(c => c.id === conversation.id)) {
            await chatStore.fetchConversations()
        }
    } catch (error) {
        console.error('Failed to start chat from contact:', error)
    }
}

async function handleAddFriend(userId: number) {
    try {
        await FriendService.sendRequest(userId)
        // Refresh users list to show updated status
        await loadUsers()
    } catch (error) {
        console.error('Failed to send friend request:', error)
    }
}

async function handleAcceptFriend(friendshipId: number) {
    if (!friendshipId) {
        console.error('Missing friendshipId for accept')
        return
    }
    try {
        await FriendService.acceptRequest(friendshipId)
        // Refresh users list to show updated status
        await loadUsers()
        // Also refresh conversations to show new friend in chats
        await chatStore.fetchConversations()
    } catch (error) {
        console.error('Failed to accept friend request:', error)
    }
}

async function handleRejectFriend(friendshipId: number) {
    if (!friendshipId) {
        console.error('Missing friendshipId for reject')
        return
    }
    try {
        await FriendService.rejectRequest(friendshipId)
        // Refresh users list to show updated status
        await loadUsers()
    } catch (error) {
        console.error('Failed to reject friend request:', error)
    }
}

async function handleCancelFriend(friendshipId: number) {
    if (!friendshipId) {
        console.error('Missing friendshipId for cancel')
        return
    }
    try {
        await FriendService.cancelRequest(friendshipId)
        // Refresh users list to show updated status
        await loadUsers()
    } catch (error) {
        console.error('Failed to cancel friend request:', error)
    }
}

async function handleSendMessage(content: string, attachments?: File[], replyToId?: number) {
    await chatStore.sendMessage(content, attachments, replyToId)
}

async function handleDeleteMessage(messageId: number) {
    try {
        await chatStore.deleteMessage(messageId)
    } catch (error) {
        console.error('Failed to delete message:', error)
    }
}

async function handleDeleteConversation(conversationId: number) {
    try {
        await chatStore.deleteConversation(conversationId)
        sidebarOpen.value = true
    } catch (error) {
        console.error('Failed to delete conversation:', error)
    }
}

async function handleLeaveGroup(conversationId: number) {
    try {
        await chatStore.leaveConversation(conversationId)
        sidebarOpen.value = true
    } catch (error) {
        console.error('Failed to leave group:', error)
    }
}

async function handleLoadMore() {
    if (messages.value.length > 0 && currentConversation.value) {
        const oldestId = messages.value[0].id
        await chatStore.fetchMessages(currentConversation.value.id, oldestId)
    }
}

function handleTyping(isTyping: boolean) {
    if (currentConversation.value) {
        sendTypingIndicator(currentConversation.value.id, isTyping)
    }
}

async function handleStartChat(userId: number) {
    showNewChat.value = false
    const conversation = await chatStore.startPrivateChat(userId)
    subscribeToConversation(conversation.id)
    activeListTab.value = 'chats'
}

async function handleCreateGroup(name: string, memberIds: number[], avatar?: File) {
    showNewChat.value = false
    const conversation = await chatStore.createGroup(name, memberIds, avatar)
    subscribeToConversation(conversation.id)
    activeListTab.value = 'groups'
}

// Scroll to a specific message in the chat window
function scrollToMessage(messageId: number) {
    // Find the message element by data attribute or id
    const messageElement = document.querySelector(`[data-message-id="${messageId}"]`)
        || document.getElementById(`message-${messageId}`)

    if (messageElement) {
        // Scroll smoothly to the message
        messageElement.scrollIntoView({ behavior: 'smooth', block: 'center' })

        // Add highlight effect temporarily
        messageElement.classList.add('message-highlight')
        setTimeout(() => {
            messageElement.classList.remove('message-highlight')
        }, 2000)
    } else {
        console.warn('ChatView: Message element not found for id', messageId)
        // If message not found, scroll to bottom (where newest messages are)
        const chatWindow = document.querySelector('.chat-window .messages-area')
        if (chatWindow) {
            chatWindow.scrollTop = chatWindow.scrollHeight
        }
    }
}

onMounted(async () => {
    // Request notification permission
    NotificationHelper.requestPermission()

    // Listen for global conversation selection (from notifications)
    // Listen for global conversation selection (from notifications)
    window.addEventListener('chat:select-conversation', async (e: any) => {
        const { conversationId } = e.detail
        // Use the smart selection action that handles fetching if needed
        await chatStore.selectConversationById(conversationId)

        // Update sidebar state if on mobile
        sidebarOpen.value = false
    })

    // Listen for select-and-scroll event (from notification clicks)
    window.addEventListener('chat:select-and-scroll', async (e: any) => {
        const { conversationId, messageId } = e.detail

        if (conversationId) {
            // Select the conversation first
            await chatStore.selectConversationById(conversationId)
            sidebarOpen.value = false

            // Wait for messages to load, then scroll to the message
            if (messageId) {
                setTimeout(() => {
                    scrollToMessage(messageId)
                }, 500)
            }
        }
    })

    // Listen for tab switch event
    window.addEventListener('chat:switch-tab', (e: any) => {
        const { tab } = e.detail
        if (tab && ['chats', 'groups', 'guest', 'contacts'].includes(tab)) {
            activeListTab.value = tab
        }
    })

    const authStore = (window as any).authStore || useAuthStore()
    if (!authStore.isInitialized) {
        await authStore.fetchUser()
    }

    if (authStore.user?.id) {
        connect(authStore.user.id)
    }

    await Promise.all([
        chatStore.fetchConversations(),
        loadUsers(),
        loadGuestConversations()
    ])
})

onUnmounted(() => {
    if (currentConversation.value) {
        unsubscribeFromConversation(currentConversation.value.id)
    }
    disconnect()
})

// Polling is now handled globally via useRealtime/useGlobalPolling
watch(currentConversation, (newConv, oldConv) => {
    if (oldConv) {
        unsubscribeFromConversation(oldConv.id)
    }
    if (newConv) {
        subscribeToConversation(newConv.id)
    }
})

// Load users when switching to Contacts tab
watch(activeListTab, (tab) => {
    if (tab === 'contacts' && allUsers.value.length === 0) {
        loadUsers()
    }
})
</script>
