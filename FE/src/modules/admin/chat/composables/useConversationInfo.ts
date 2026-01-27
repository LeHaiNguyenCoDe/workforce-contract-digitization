/**
 * Shared Conversation Info Composable
 * Extracts common avatar/name/status logic used across ChatWindow and ConversationDetails
 */
import { computed, type Ref, type ComputedRef } from 'vue'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores/auth'
import { useChatStore } from '../stores/chatStore'
import type { IConversation, IConversationUser } from '../models/Chat'

// ============================================
// Constants
// ============================================
const AVATAR_COLORS = [
  'bg-teal-500',
  'bg-blue-500',
  'bg-purple-500',
  'bg-pink-500',
  'bg-orange-500'
]

// ============================================
// Types
// ============================================
export interface ConversationInfo {
  name: ComputedRef<string>
  avatar: ComputedRef<string | null>
  initials: ComputedRef<string>
  avatarColor: ComputedRef<string>
  status: ComputedRef<string>
  isOnline: ComputedRef<boolean>
  partner: ComputedRef<IConversationUser | null>
  isGuestChat: ComputedRef<boolean>
  memberCount: ComputedRef<number>
}

// ============================================
// Helper Functions
// ============================================

/**
 * Get initials from a name (max 2 characters)
 */
export function getInitials(name: string): string {
  return name
    .split(' ')
    .map(word => word[0])
    .join('')
    .substring(0, 2)
    .toUpperCase()
}

/**
 * Get avatar color based on conversation ID
 */
export function getAvatarColorById(id: number): string {
  return AVATAR_COLORS[id % AVATAR_COLORS.length]
}

/**
 * Format typing indicator text
 */
export function formatTypingText(
  typingUsers: string[],
  t: (key: string) => string
): string {
  if (typingUsers.length === 0) return ''
  if (typingUsers.length === 1) return `${typingUsers[0]} ${t('common.chat.is_typing')}`
  if (typingUsers.length === 2) return `${typingUsers.join(' & ')} ${t('common.chat.are_typing')}`
  return t('common.chat.several_typing')
}

// ============================================
// Main Composable
// ============================================

/**
 * Extract conversation display information
 * Used by ChatWindow.vue and ConversationDetails.vue to avoid code duplication
 *
 * @param conversation - Reactive reference to the conversation
 * @returns Object with computed properties for display
 */
export function useConversationInfo(
  conversation: Ref<IConversation | null> | ComputedRef<IConversation | null>
): ConversationInfo {
  const { t } = useI18n()
  const authStore = useAuthStore()
  const chatStore = useChatStore()

  /**
   * Get the other user in a private conversation
   */
  const partner = computed<IConversationUser | null>(() => {
    if (!conversation.value) return null
    if (conversation.value.type !== 'private') return null

    const currentUserId = authStore.user?.id
    return conversation.value.users?.find(u => u.id !== currentUserId) || null
  })

  /**
   * Is this a guest chat?
   */
  const isGuestChat = computed(() => {
    return !!conversation.value?.is_guest
  })

  /**
   * Get conversation display name
   */
  const name = computed<string>(() => {
    if (!conversation.value) return t('common.chat.no_name')

    // Use conversation name if set
    if (conversation.value.name) return conversation.value.name

    // For guest chats
    if (isGuestChat.value && conversation.value.guest_session?.guest_name) {
      return conversation.value.guest_session.guest_name
    }

    // For private conversations, use partner's name
    if (partner.value) {
      return partner.value.name
    }

    // For groups without a name, combine member names
    const users = conversation.value.users
    if (!users || users.length === 0) return t('common.chat.no_name')

    if (users.length === 1) return users[0].name

    return users.map(u => u.name.split(' ')[0]).join(', ')
  })

  /**
   * Get conversation avatar URL
   */
  const avatar = computed<string | null>(() => {
    if (!conversation.value) return null

    // Use conversation avatar if set
    if (conversation.value.avatar) return conversation.value.avatar

    // For private conversations, use partner's avatar
    if (partner.value?.avatar) {
      return partner.value.avatar
    }

    return null
  })

  /**
   * Get initials for avatar fallback
   */
  const initials = computed<string>(() => {
    return getInitials(name.value)
  })

  /**
   * Get avatar background color
   */
  const avatarColor = computed<string>(() => {
    if (!conversation.value) return AVATAR_COLORS[0]
    return getAvatarColorById(conversation.value.id)
  })

  /**
   * Is the conversation partner online?
   */
  /**
   * Is the conversation partner online?
   */
  const isOnline = computed<boolean>(() => {
    if (!conversation.value) return false
    
    // For private conversations, check partner's ID in onlineUserIds
    if (conversation.value.type === 'private') {
      return partner.value ? chatStore.isUserOnline(partner.value.id) : false
    }
    
    // For group conversations, return true if any member is online
    return conversation.value.users?.some(u => {
      const currentUserId = authStore.user?.id
      return u.id !== currentUserId && chatStore.isUserOnline(u.id)
    }) || false
  })

  /**
   * Get status text
   */
  const status = computed<string>(() => {
    if (!conversation.value) return ''

    // Guest chat
    if (isGuestChat.value) {
      return 'Khách hàng'
    }

    // Private conversation
    if (conversation.value.type === 'private') {
      return isOnline.value ? t('common.chat.online') : t('common.chat.offline')
    }

    // Group conversation
    const memberCount = conversation.value.users?.length || 0
    const onlineCount = conversation.value.users?.filter(u => {
      const currentUserId = authStore.user?.id
      return u.id !== currentUserId && chatStore.isUserOnline(u.id)
    }).length || 0
    
    if (onlineCount > 0) {
      return `${onlineCount}/${memberCount} ${t('common.chat.online')}`
    }
    
    return `${memberCount} ${t('common.chat.members')}`
  })

  /**
   * Get member count for groups
   */
  const memberCount = computed<number>(() => {
    return conversation.value?.users?.length || 0
  })

  return {
    name,
    avatar,
    initials,
    avatarColor,
    status,
    isOnline,
    partner,
    isGuestChat,
    memberCount
  }
}

// ============================================
// Typing Indicator Composable
// ============================================

export interface TypingInfo {
  typingText: ComputedRef<string>
  isTyping: ComputedRef<boolean>
}

/**
 * Format typing indicator for display
 */
export function useTypingIndicator(typingUsers: Ref<string[]>): TypingInfo {
  const { t } = useI18n()

  const isTyping = computed(() => typingUsers.value.length > 0)

  const typingText = computed(() => {
    return formatTypingText(typingUsers.value, t)
  })

  return {
    typingText,
    isTyping
  }
}
