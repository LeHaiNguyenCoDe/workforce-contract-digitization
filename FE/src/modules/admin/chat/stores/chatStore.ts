import { defineStore } from 'pinia'
import { ref, computed, onUnmounted } from 'vue'
import type { IConversation, IMessage } from '../models/Chat'
import { ChatService } from '../services/ChatService'
import { GuestChatService } from '@/modules/landing/chat/services/GuestChatService'

export const useChatStore = defineStore('chat', () => {
  // Listen for logout events to reset store
  const logoutHandler = () => {
    $reset()
  }
  window.addEventListener('auth:logout', logoutHandler)
  // State
  const conversations = ref<IConversation[]>([])
  const currentConversation = ref<IConversation | null>(null)
  const messages = ref<IMessage[]>([])
  const isLoadingConversations = ref(false)
  const isLoadingMessages = ref(false)
  const isSendingMessage = ref(false)
  const hasMoreMessages = ref(true)
  const typingUsers = ref<Map<number, { userId: number; userName: string }>>(new Map())

  // Getters
  const totalUnreadCount = computed(() => {
    return conversations.value.reduce((sum, conv) => sum + (conv.unread_count || 0), 0)
  })

  const sortedConversations = computed(() => {
    return [...conversations.value].sort((a, b) => {
      // Pinned first
      const aPinned = a.pivot?.is_pinned ? 1 : 0
      const bPinned = b.pivot?.is_pinned ? 1 : 0
      if (aPinned !== bPinned) return bPinned - aPinned

      // Then by latest message
      const aDate = a.latest_message?.created_at || a.updated_at
      const bDate = b.latest_message?.created_at || b.updated_at
      return new Date(bDate).getTime() - new Date(aDate).getTime()
    })
  })

  const currentConversationPartner = computed(() => {
    if (!currentConversation.value || currentConversation.value.type !== 'private') {
      return null
    }
    // Return the other user in a private conversation
    const currentUserId = parseInt(localStorage.getItem('userId') || '0')
    return currentConversation.value.users.find(u => u.id !== currentUserId) || null
  })

  // Actions
  async function fetchConversations(page = 1, silent = false) {
    if (!silent) isLoadingConversations.value = true
    try {
      const response = await ChatService.getConversations(page)
      if (page === 1) {
        conversations.value = response.data
      } else {
        conversations.value.push(...response.data)
      }
      return response
    } finally {
      if (!silent) isLoadingConversations.value = false
    }
  }

  async function fetchMessages(conversationId: number, beforeId?: number, silent = false) {
    if (!silent) isLoadingMessages.value = true
    try {
      const newMessages = await ChatService.getMessages(conversationId, 50, beforeId)
      if (beforeId) {
        messages.value = [...newMessages, ...messages.value]
      } else {
        messages.value = newMessages
      }
      hasMoreMessages.value = newMessages.length >= 50
      return newMessages
    } finally {
      if (!silent) isLoadingMessages.value = false
    }
  }

  async function sendMessage(content: string, attachments?: File[], replyToId?: number) {
    if (!currentConversation.value) return null

    isSendingMessage.value = true
    try {
      const message = await ChatService.sendMessage(currentConversation.value.id, {
        content,
        attachments,
        reply_to_id: replyToId
      })
      // Message might have already been added via WebSocket during the API call
      // Avoid duplicates with explicit casting for safety
      const isDuplicate = messages.value.some(m => Number(m.id) === Number(message.id))
      if (!isDuplicate) {
          messages.value.push(message)
      }
      return message
    } finally {
      isSendingMessage.value = false
    }
  }

  async function startPrivateChat(userId: number) {
    const conversation = await ChatService.startPrivateChat(userId)
    // Check if already in list
    const existing = conversations.value.find(c => c.id === conversation.id)
    if (!existing) {
      conversations.value.unshift(conversation)
    }
    selectConversation(conversation)
    return conversation
  }

  async function createGroup(name: string, memberIds: number[], avatar?: File) {
    const conversation = await ChatService.createGroup({ name, member_ids: memberIds, avatar })
    conversations.value.unshift(conversation)
    selectConversation(conversation)
    return conversation
  }

  async function deleteMessage(messageId: number) {
    await ChatService.deleteMessage(messageId)
    messages.value = messages.value.filter(m => m.id !== messageId)
  }

  async function deleteConversation(conversationId: number) {
    await ChatService.deleteConversation(conversationId)
    conversations.value = conversations.value.filter(c => c.id !== conversationId)
    if (currentConversation.value?.id === conversationId) {
      currentConversation.value = null
      messages.value = []
    }
  }

  async function leaveConversation(conversationId: number) {
    await ChatService.leaveConversation(conversationId)
    conversations.value = conversations.value.filter(c => c.id !== conversationId)
    if (currentConversation.value?.id === conversationId) {
      currentConversation.value = null
      messages.value = []
    }
  }

  async function assignGuestChat(sessionToken: string, staffId: number) {
    try {
      const staff = await GuestChatService.assignStaff(sessionToken, staffId)
      // Update current conversation 
      if (currentConversation.value) {
        // Update guest_session.assigned_staff
        if (currentConversation.value.guest_session) {
          currentConversation.value.guest_session.assigned_staff = {
            id: staff.id,
            name: staff.name,
            avatar: staff.avatar
          }
        }
        // Also add staff to conversation.users if not there
        if (!currentConversation.value.users.some(u => u.id === staffId)) {
          currentConversation.value.users.push({
            id: staff.id,
            name: staff.name,
            avatar: staff.avatar,
            role: 'member'
          } as any)
        }
      }
      // Also update in conversations list
      const convInList = conversations.value.find(c => c.guest_session?.session_token === sessionToken)
      if (convInList?.guest_session) {
        convInList.guest_session.assigned_staff = {
          id: staff.id,
          name: staff.name,
          avatar: staff.avatar
        }
      }
      return staff
    } catch (error) {
      console.error('ChatStore: Failed to assign guest chat', error)
      throw error
    }
  }

  async function selectConversationById(conversationId: number) {
    // 1. Check if already in list
    let conversation = conversations.value.find(c => c.id === conversationId)

    // 2. If not, fetch it
    if (!conversation) {
      isLoadingConversations.value = true
      try {
        conversation = await ChatService.getConversation(conversationId)
        // Add to list if valid
        if (conversation) {
          conversations.value.unshift(conversation)
        }
      } catch (error) {
        console.error('ChatStore: Failed to fetch conversation', conversationId, error)
        return
      } finally {
        isLoadingConversations.value = false
      }
    }

    // 3. Select it
    if (conversation) {
      selectConversation(conversation)
    }
  }

  function selectConversation(conversation: IConversation) {
    currentConversation.value = conversation
    messages.value = []
    hasMoreMessages.value = true
    fetchMessages(conversation.id)
    ChatService.markAsRead(conversation.id)
    // Reset unread count
    const conv = conversations.value.find(c => c.id === conversation.id)
    if (conv) {
      conv.unread_count = 0
    }
  }

  // WebSocket handlers
  async function handleNewMessage(message: IMessage) {
    // 1. Check if conversation is in the list
    let conv = conversations.value.find(c => c.id === message.conversation_id)

    // 2. If not, fetch it from backend
    if (!conv) {
      try {
        conv = await ChatService.getConversation(message.conversation_id)
        if (conv) {
          conversations.value = [conv, ...conversations.value]
          // The fetched conversation already has the latest message and unread count from server
        }
      } catch (error) {
        console.error('ChatStore: Failed to fetch missing conversation', error)
        return
      }
    } else {
      // 3. For existing conversation, update unread count and latest message
      const isCurrentUser = Number(message.user_id) === (window as any).authStore?.user?.id
      
      if (currentConversation.value?.id === message.conversation_id) {
        // Avoid duplicates in current window
        const isDuplicate = messages.value.some(m => Number(m.id) === Number(message.id))
        if (!isDuplicate) {
          messages.value = [...messages.value, message]
          
          // Emit event for UI (Smart Scroll)
          window.dispatchEvent(new CustomEvent('chat:new-message-arrived', { 
            detail: { message, isFromSelf: isCurrentUser } 
          }))
        }
        // Mark as read since we're viewing
        ChatService.markAsRead(message.conversation_id)
      } else {
        // Increment unread count for other tabs/conversations
        const isDuplicate = conv.latest_message?.id === message.id
        if (!isDuplicate) {
          conv.unread_count = (conv.unread_count || 0) + 1
          
          // Dispatch toast if from others
          if (!isCurrentUser) {
            window.dispatchEvent(new CustomEvent('notification:show-toast', { 
              detail: { ...message, type: 'message' } 
            }))
          }
        }
      }
      conv.latest_message = message
    }

    // 4. Move conversation to top if it's not already
    const index = conversations.value.findIndex(c => c.id === message.conversation_id)
    if (index > 0) {
      const [c] = conversations.value.splice(index, 1)
      conversations.value.unshift(c)
    }
  }

  function handleUserTyping(_conversationId: number, userId: number, userName: string, isTyping: boolean) {
    if (isTyping) {
      typingUsers.value.set(userId, { userId, userName })
    } else {
      typingUsers.value.delete(userId)
    }
  }

  function clearTypingUsers() {
    typingUsers.value.clear()
  }

  function $reset() {
    conversations.value = []
    currentConversation.value = null
    messages.value = []
    isLoadingConversations.value = false
    isLoadingMessages.value = false
    isSendingMessage.value = false
    hasMoreMessages.value = true
    typingUsers.value.clear()
  }

  return {
    // State
    conversations,
    currentConversation,
    messages,
    isLoadingConversations,
    isLoadingMessages,
    isSendingMessage,
    hasMoreMessages,
    typingUsers,

    // Getters
    totalUnreadCount,
    sortedConversations,
    currentConversationPartner,

    // Actions
    fetchConversations,
    fetchMessages,
    sendMessage,
    startPrivateChat,
    createGroup,
    deleteMessage,
    deleteConversation,
    leaveConversation,
    selectConversation,
    selectConversationById,
    selectConversationId: selectConversationById,
    assignGuestChat,
    handleNewMessage,
    handleUserTyping,
    clearTypingUsers,
    $reset
  }
})
