<script setup lang="ts">
/**
 * Chat Bubble Component
 * Floating chat button on landing page
 */
import { computed } from 'vue'
import { useLandingChatStore } from '../stores/landingChatStore'
import ChatPopup from './ChatPopup.vue'

const chatStore = useLandingChatStore()

const isOpen = computed(() => chatStore.isOpen)
const unreadCount = computed(() => chatStore.unreadCount)

function toggleChat() {
  chatStore.toggleChat()
}
</script>

<template>
  <div class="chat-bubble-container">
    <!-- Chat Popup -->
    <Transition name="popup">
      <ChatPopup v-if="isOpen" @close="toggleChat" />
    </Transition>

    <!-- Floating Bubble Button -->
    <button 
      class="chat-bubble"
      @click="toggleChat"
      :class="{ 'chat-bubble--active': isOpen }"
      aria-label="Open chat"
    >
      <!-- Chat Icon -->
      <svg v-if="!isOpen" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 2C6.477 2 2 6.145 2 11.243c0 2.936 1.525 5.55 3.897 7.217-.054 2.137-.467 3.461-.467 3.461s2.493-.798 4.086-1.632c.788.153 1.614.234 2.484.234 5.523 0 10-4.145 10-9.257C22 6.145 17.523 2 12 2z"/>
      </svg>
      
      <!-- Close Icon -->
      <svg v-else xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="18" y1="6" x2="6" y2="18"></line>
        <line x1="6" y1="6" x2="18" y2="18"></line>
      </svg>

      <!-- Unread Badge -->
      <span v-if="unreadCount > 0 && !isOpen" class="badge">
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
    </button>
  </div>
</template>

