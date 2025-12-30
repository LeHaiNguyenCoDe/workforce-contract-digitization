<script setup lang="ts">
/**
 * Chat Popup Component
 * Main chat dialog with messages and input
 */
import { ref, computed, nextTick, watch, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useLandingChatStore } from '../stores/landingChatStore'
import GuestInfoForm from './GuestInfoForm.vue'
import EmojiPicker from './EmojiPicker.vue'
import { sanitizeSimpleHtml } from '@/shared/utils/sanitize'

const emit = defineEmits<{
  close: []
}>()

const router = useRouter()
const chatStore = useLandingChatStore()

const messageInput = ref('')
const chatBody = ref<HTMLElement | null>(null)
let pollingInterval: ReturnType<typeof setInterval> | null = null

const hasSession = computed(() => chatStore.hasSession)
const guestInfo = computed(() => chatStore.guestInfo)
const assignedStaff = computed(() => chatStore.assignedStaff)
const messages = computed(() => chatStore.messages)
const isLoading = computed(() => chatStore.isLoading)
const isSending = computed(() => chatStore.isSending)

const selectedFiles = ref<File[]>([])
const showEmojiPicker = ref(false)
const globalURL = window.URL || URL

// Image selection logic

function onFileSelected(e: Event) {
  const target = e.target as HTMLInputElement
  if (target.files) {
    const files = Array.from(target.files)
    selectedFiles.value = [...selectedFiles.value, ...files]
    
    files.forEach(file => {
      if (file.type.startsWith('image/')) {
        const reader = new FileReader()
        reader.onload = (e) => {
          // filePreviews.value.push(e.target?.result as string) // Removed as per instruction
        }
        reader.readAsDataURL(file)
      }
    })
  }
}

function removeFile(index: number) {
  selectedFiles.value.splice(index, 1)
  // filePreviews.value.splice(index, 1) // Removed as per instruction
}

// Scroll to bottom when content or layout changes
watch(
  [() => messages.value.length, () => chatStore.isTyping, () => selectedFiles.value.length],
  async () => {
    await nextTick()
    scrollToBottom()
  },
  { immediate: true }
)

// Start polling when we have a session
watch(hasSession, (has) => {
  if (has) {
    startPolling()
  } else {
    stopPolling()
  }
}, { immediate: true })

function startPolling() {
  if (pollingInterval) return
  // Poll every 3 seconds for new messages
  pollingInterval = setInterval(() => {
    if (hasSession.value) {
      chatStore.loadMessages(true) // silent = true to avoid showing spinner
    }
  }, 3000)
}

function stopPolling() {
  if (pollingInterval) {
    clearInterval(pollingInterval)
    pollingInterval = null
  }
}

onMounted(() => {
  if (hasSession.value) {
    chatStore.loadMessages()
    chatStore.loadSessionInfo()
    startPolling()
  }
  scrollToBottom()
})

onUnmounted(() => {
  stopPolling()
})

function scrollToBottom() {
  if (chatBody.value) {
    chatBody.value.scrollTo({
      top: chatBody.value.scrollHeight,
      behavior: 'auto' // Use auto for instant jump, or 'smooth' if desired but 'auto' is more reliable for updates
    })
  }
}

async function sendMessage() {
  const content = messageInput.value.trim()
  const files = selectedFiles.value
  
  if ((!content && files.length === 0) || isSending.value) return
  
  // Check for navigation intent BEFORE sending
  const navigationProductId = chatStore.checkProductNavigation(content)

  messageInput.value = ''
  selectedFiles.value = []
  // filePreviews.value = [] // Removed as per instruction
  
  await chatStore.sendMessage(content, files)

  if (navigationProductId) {
    router.push({ name: 'product-detail', params: { id: navigationProductId } })
  }
}

function handleKeydown(e: KeyboardEvent) {
  if (e.key === 'Enter' && !e.shiftKey) {
    e.preventDefault()
    sendMessage()
  }
}

function onSessionStarted() {
  // Session created, now ready to chat
  scrollToBottom()
}

function insertEmoji(emoji: string) {
  messageInput.value += emoji
  showEmojiPicker.value = false
}

function formatTime(dateStr: string): string {
  const date = new Date(dateStr)
  return date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })
}

/**
 * Normalizes file paths for the frontend
 * Handles URLs from Laravel backend (asset() helper) which may have different domains
 */
function getFileUrl(url: string | undefined): string {
  if (!url) return ''
  
  // Data URLs are already complete
  if (url.startsWith('data:')) return url
  
  // If URL contains /storage/, extract the path and use Vite proxy
  // This handles URLs like: http://workforce_contract_digitization.io/storage/chat/attachments/file.png
  if (url.includes('/storage/')) {
    const storageIndex = url.indexOf('/storage/')
    return url.substring(storageIndex)
  }
  
  // Absolute URLs without /storage/ - return as-is (external images)
  if (url.startsWith('http://') || url.startsWith('https://')) {
    return url
  }
  
  // Handle relative paths like 'chat/attachments/file.png' or '/chat/attachments/file.png'
  const cleanPath = url.replace(/^\//, '').replace(/^storage\//, '')
  return `/storage/${cleanPath}`
}
</script>

<template>
  <div class="chat-popup">
    <!-- Header -->
    <header class="chat-header">
      <div class="chat-header__left">
        <div class="avatar" v-if="assignedStaff?.avatar">
          <img :src="getFileUrl(assignedStaff.avatar)" :alt="assignedStaff.name" />
        </div>
        <div class="avatar waiting" v-else-if="!assignedStaff">
          <div class="pulse-ring"></div>
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#fff">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
          </svg>
        </div>
        <div class="avatar" v-else>
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#fff">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
          </svg>
        </div>
        <div class="info">
          <h3>{{ assignedStaff?.name || 'Vui lòng đợi...' }}</h3>
          <span class="status" v-if="assignedStaff">
            <span class="status-dot"></span>
            Trực tuyến
          </span>
          <span class="status waiting" v-else>
            <span class="status-dot pulse"></span>
            Đang đợi nhân viên...
          </span>
        </div>
      </div>
      <div class="chat-header__actions">
        <button class="action-btn" @click="emit('close')" title="Đóng">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
      </div>
    </header>

    <!-- Body -->
    <div class="chat-body" ref="chatBody">
      <!-- Guest Info Form (if no session) -->
      <template v-if="!hasSession">
        <div class="welcome-message">
          <div class="message message--bot">
            <div class="message-content">
              <p>Xin chào! 👋</p>
              <p>Mình là trợ lý hỗ trợ. Mình có thể giúp gì cho bạn?</p>
              <p class="welcome-hint">Vui lòng cho mình biết thông tin để bắt đầu chat:</p>
            </div>
          </div>
        </div>
        <GuestInfoForm @session-started="onSessionStarted" />
      </template>

      <!-- Messages -->
      <template v-else>
        <div class="messages">
          <!-- Welcome message -->
          <div class="message message--bot welcome-bubble">
            <div class="message-content">
              <p>Xin chào <strong>{{ guestInfo?.name }}</strong>! 👋</p>
              <p>Mình có thể hỗ trợ được gì cho bạn?</p>
            </div>
          </div>

          <!-- Loading -->
          <div v-if="isLoading" class="loading">
            <div class="spinner"></div>
          </div>

          <!-- Message list -->
          <div 
            v-for="message in messages" 
            :key="message.id"
            class="message"
            :class="{ 
              'message--user': message.is_guest,
              'message--bot': !message.is_guest,
              'message--image': message.type === 'image'
            }"
          >
            <div class="message-content">
              <div v-if="message.attachments && message.attachments.length > 0" class="message-attachments">
                <div v-for="att in message.attachments" :key="att.id" class="attachment-item">
                  <img 
                    v-if="att.file_type.startsWith('image/')" 
                    :src="getFileUrl(att.file_path)" 
                    :alt="att.file_name" 
                    class="img-preview" 
                    @load="scrollToBottom"
                  />
                  <div v-else-if="att.file_type.startsWith('audio/') || att.file_name.endsWith('.webm') || att.file_name.endsWith('.mp3') || att.file_name.endsWith('.wav')" class="audio-attachment">
                    <div class="audio-info">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z" />
                        <path d="M19 10v2a7 7 0 0 1-14 0v-2" />
                        <line x1="12" x2="12" y1="19" y2="22" />
                      </svg>
                      <span class="audio-name">{{ att.file_name }}</span>
                    </div>
                    <audio controls :src="getFileUrl(att.file_path)" class="custom-audio"></audio>
                  </div>
                  <a v-else :href="getFileUrl(att.file_path)" target="_blank" class="file-link">{{ att.file_name }}</a>
                </div>
              </div>
              <p v-if="message.content" v-html="sanitizeSimpleHtml(message.content)"></p>
            </div>
            <span class="time">{{ formatTime(message.created_at) }}</span>
          </div>

          <!-- Typing indicator -->
          <div v-if="chatStore.isTyping" class="message message--bot typing">
            <div class="message-content">
              <span class="typing-dot"></span>
              <span class="typing-dot"></span>
              <span class="typing-dot"></span>
            </div>
          </div>
        </div>
      </template>
    </div>

    <!-- Footer / Input -->
    <footer v-if="hasSession" class="chat-footer">
      <!-- Normal Input -->
      <!-- Image Preview -->
      <div v-if="selectedFiles.length > 0" class="previews-container">
        <div v-for="(file, index) in selectedFiles" :key="index" class="preview-item">
          <img v-if="file.type.startsWith('image/')" :src="globalURL.createObjectURL(file)" />
          <div v-else class="file-icon">📄</div>
          <button class="remove-btn" @click="removeFile(index)">×</button>
        </div>
      </div>

      <div class="chat-footer__actions">
        <div class="action-buttons-left">
          <label class="action-btn cursor-pointer" title="Chọn ảnh">
            <input type="file" accept="image/*" multiple class="hidden" @change="onFileSelected" />
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
              <circle cx="9" cy="9" r="2" />
              <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
            </svg>
          </label>
        </div>

        <div class="input-wrapper">
          <input v-model="messageInput" @keydown="handleKeydown" :disabled="isSending" type="text" placeholder="Aa" />
          <button @click="showEmojiPicker = !showEmojiPicker" class="emoji-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10" />
              <path d="M8 14s1.5 2 4 2 4-2 4-2" />
              <line x1="9" y1="9" x2="9.01" y2="9" />
              <line x1="15" y1="9" x2="15.01" y2="9" />
            </svg>
          </button>
          <div v-if="showEmojiPicker" class="picker-popup picker-popup--right">
            <EmojiPicker @select="insertEmoji" @close="showEmojiPicker = false" />
          </div>
        </div>

        <button @click="sendMessage" :disabled="isSending || (!messageInput.trim() && selectedFiles.length === 0)" class="send-btn">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z" />
          </svg>
        </button>
      </div>
      <p class="powered-by">Powered by <a href="#" target="_blank">Ceramic ERP</a></p>
    </footer>
  </div>
</template>

<style scoped lang="scss">
.chat-popup {
  position: fixed;
  bottom: 100px;
  right: 24px;
  width: 380px;
  max-height: 600px;
  background: #ffffff;
  border-radius: 16px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  z-index: 9998;
}

.hidden {
  display: none;
}

// Header
.chat-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
  color: white;
  
  &__left {
    display: flex;
    align-items: center;
    gap: 12px;
  }
  
  .avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    overflow: hidden;
    border: 2px solid rgba(255, 255, 255, 0.2);
    
    img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
  }
  
  .info {
    h3 {
      font-size: 0.95rem;
      font-weight: 600;
      margin: 0;
    }
    
    .status {
      font-size: 0.7rem;
      display: flex;
      align-items: center;
      gap: 4px;
      opacity: 0.9;
    }
    
    .status-dot {
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: #22c55e;
    }
  }
  
  &__actions {
    display: flex;
    gap: 4px;
  }
  
  .action-btn {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: none;
    background: rgba(255, 255, 255, 0.1);
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
    
    &:hover {
      background: rgba(255, 255, 255, 0.2);
    }
  }
}

// Body
.chat-body {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
  background: #f8f9fa;
  min-height: 300px;
  // Removed scroll-behavior: smooth to avoid jumping conflicts during rapid updates
}

.messages {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.message {
  max-width: 85%;
  font-size: 0.95rem;
  line-height: 1.6;
  position: relative;
  
  .message-content {
    padding: 12px 16px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  }
  
  p {
    margin: 0;
    word-break: break-word;
    
    & + p {
      margin-top: 6px;
    }
  }

  .welcome-hint {
    margin-top: 10px;
    font-size: 0.85rem;
    color: #6b7280;
    font-weight: 500;
  }
  
  &--bot {
    align-self: flex-start;
    .message-content {
      background: white;
      color: #0c0a09; // Maximum contrast black
      font-weight: 500; // Medium weight for better legibility
      border-bottom-left-radius: 2px;
      border: 1px solid #d1d5db; // Stronger border
    }
  }
  
  &--user {
    align-self: flex-end;
    .message-content {
      background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
      color: white;
      border-bottom-right-radius: 2px;
      box-shadow: 0 6px 16px rgba(124, 58, 237, 0.25);
    }
    .time {
      text-align: right;
    }
  }

  .message-attachments {
    margin-bottom: 8px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;

    .img-preview {
      max-width: 100%;
      border-radius: 8px;
      max-height: 200px;
      display: block;
    }

    .file-link {
      color: inherit;
      text-decoration: underline;
      font-size: 0.85rem;
    }
  }
  
  .time {
    display: block;
    font-size: 0.7rem;
    opacity: 0.5;
    margin-top: 4px;
    padding: 0 4px;
  }
}

.welcome-message {
  margin-bottom: 24px;
}

// Typing indicator
.typing {
  .message-content {
    padding: 10px 16px;
    background: white;
    display: flex;
    align-items: center;
    gap: 4px;
  }
  
  .typing-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #9ca3af;
    animation: typing 1.4s infinite ease-in-out both;
    
    &:nth-child(1) { animation-delay: -0.32s; }
    &:nth-child(2) { animation-delay: -0.16s; }
  }
}

@keyframes typing {
  0%, 80%, 100% { transform: scale(0.6); opacity: 0.6; }
  40% { transform: scale(1); opacity: 1; }
}

// Footer
.chat-footer {
  padding: 12px 16px;
  border-top: 1px solid #e5e7eb;
  background: white;
  
  .previews-container {
    display: flex;
    gap: 8px;
    padding-bottom: 12px;
    overflow-x: auto;

    .preview-item {
      position: relative;
      width: 60px;
      height: 60px;
      flex-shrink: 0;

      img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
      }

      .file-icon {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f0f2f5;
        border-radius: 8px;
        font-size: 24px;
        color: #6b7280;
      }

      .remove-btn {
        position: absolute;
        top: -6px;
        right: -6px;
        width: 18px;
        height: 18px;
        background: #ef4444;
        color: white;
        border: none;
        border-radius: 50%;
        font-size: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        padding: 0;
      }
    }
  }

  .powered-by {
    text-align: center;
    font-size: 0.7rem;
    color: #9ca3af;
    margin: 8px 0 0;
    
    a { color: #7c3aed; text-decoration: none; &:hover { text-decoration: underline; } }
  }

  .chat-footer__actions {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
}

.action-buttons-left {
  display: flex;
  align-items: center;
  gap: 4px;
}

.action-btn {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #0ea5e9;
  border-radius: 50%;
  transition: background-color 0.2s;
  flex-shrink: 0;
  cursor: pointer;
  position: relative;
  transition: color 0.2s;

  &:hover {
    color: #4f46e5;
    background-color: transparent;
  }

  &:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }
}

.input-wrapper {
  flex: 1;
  position: relative;
  display: flex;
  align-items: center;
  background-color: #f3f4f6;
  border-radius: 20px;
  padding: 0 12px;
  height: 36px;

  input {
    flex: 1;
    border: none;
    background: transparent;
    padding: 8px 0;
    font-size: 14px;
    outline: none;
    color: #374151;
  }

  .emoji-btn {
    color: #0ea5e9;
    padding: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.2s;
    border: none;
    background: transparent;
    cursor: pointer;

    &:hover {
      transform: scale(1.1);
    }
  }
}

.picker-popup {
  position: absolute;
  bottom: 48px;
  z-index: 100;
  
  &--left {
    left: 0;
  }
  
  &--right {
    right: 0;
  }
}

.gif-badge {
  position: absolute;
  bottom: 2px;
  right: 2px;
  background: #0ea5e9;
  color: white;
  font-size: 7px;
  font-weight: bold;
  padding: 1px 2px;
  border-radius: 3px;
  line-height: 1;
}

.send-btn {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: transparent;
  color: #0ea5e9;
  border-radius: 50%;
  transition: all 0.2s;
  flex-shrink: 0;
  border: none;
  cursor: pointer;

  &:hover:not(:disabled) {
    background-color: #f0f9ff;
    transform: scale(1.05);
  }

  &:disabled {
    color: #9ca3af;
    cursor: not-allowed;
  }
}
}

.custom-audio {
  height: 32px;
  filter: invert(0) grayscale(0) contrast(1);
  border-radius: 16px;
  background: transparent;
  width: 100%;

  &::-webkit-audio-controls-panel {
    background-color: #f3f4f6;
  }
}

.audio-attachment {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding: 8px;
  background: #f9fafb;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
  min-width: 220px;

  .audio-info {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #374151;
    font-size: 0.8rem;
    font-weight: 600;

    .audio-name {
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
  }
}

.message--user .audio-attachment {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.2);
  color: white;

  .audio-info {
    color: white;
  }
  
  .custom-audio::-webkit-audio-controls-panel {
    background-color: rgba(255, 255, 255, 0.8);
  }
}

// Mobile responsive
@media (max-width: 640px) {
  .chat-popup {
    position: fixed;
    inset: 0;
    width: 100%;
    max-height: 100vh;
    border-radius: 0;
    bottom: 0;
    right: 0;
  }
}
.status-dot.pulse {
  background-color: #ff9800;
  box-shadow: 0 0 0 0 rgba(255, 152, 0, 0.4);
  animation: pulse-dot 1.5s infinite;
}

@keyframes pulse-dot {
  0% {
    transform: scale(0.95);
    box-shadow: 0 0 0 0 rgba(255, 152, 0, 0.7);
  }
  70% {
    transform: scale(1);
    box-shadow: 0 0 0 10px rgba(255, 152, 0, 0);
  }
  100% {
    transform: scale(0.95);
    box-shadow: 0 0 0 0 rgba(255, 152, 0, 0);
  }
}

.avatar.waiting {
  background: linear-gradient(135deg, #ff9800, #f44336);
  position: relative;
}

.pulse-ring {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  border-radius: 50%;
  border: 2px solid #ff9800;
  animation: pulse-ring 2s infinite;
}

@keyframes pulse-ring {
  0% {
    transform: scale(0.8);
    opacity: 0.8;
  }
  100% {
    transform: scale(1.6);
    opacity: 0;
  }
}

.status.waiting {
  color: #ff9800;
  font-weight: 500;
}

</style>
