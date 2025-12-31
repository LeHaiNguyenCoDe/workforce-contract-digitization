<template>
    <div class="bg-white border-t border-gray-100 p-3 sm:p-4 flex-shrink-0">
        <!-- Reply Preview -->
        <div v-if="replyTo && !isRecording"
            class="mb-3 p-3 bg-gray-50 rounded-lg border-l-2 border-teal-500 flex items-start justify-between gap-2">
            <div class="min-w-0">
                <p class="text-xs font-medium text-teal-600 mb-0.5">{{ t('common.chat.replying_to') }} {{
                    replyTo.user?.name }}
                </p>
                <p class="text-sm text-gray-500 truncate">{{ replyTo.content }}</p>
            </div>
            <button @click="$emit('cancel-reply')"
                class="flex-shrink-0 text-gray-400 hover:text-gray-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
            </button>
        </div>

        <!-- Attachments Preview -->
        <div v-if="attachments.length > 0 && !isRecording" class="mb-3 flex flex-wrap gap-2">
            <div v-for="(file, index) in attachments" :key="index" class="relative group">
                <img v-if="file.type.startsWith('image')" :src="getPreviewUrl(file)"
                    class="w-16 h-16 sm:w-20 sm:h-20 object-cover rounded-lg border border-gray-200" />
                <div v-else-if="file.type.startsWith('audio')"
                    class="w-16 h-16 sm:w-20 sm:h-20 bg-teal-50 rounded-lg flex items-center justify-center border border-teal-200">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" class="text-teal-500">
                        <path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z" />
                        <path d="M19 10v2a7 7 0 0 1-14 0v-2" />
                        <line x1="12" x2="12" y1="19" y2="22" />
                    </svg>
                </div>
                <div v-else
                    class="w-16 h-16 sm:w-20 sm:h-20 bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" class="text-gray-400">
                        <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z" />
                        <polyline points="13 2 13 9 20 9" />
                    </svg>
                </div>
                <button @click="removeAttachment(index)"
                    class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-red-500 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none"
                        stroke="white" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Voice Recording UI -->
        <VoiceRecordingUI v-if="isRecording" :formattedDuration="voiceRecorder.formattedDuration.value"
            :audioLevels="voiceRecorder.audioLevels.value" @cancel="cancelVoiceRecording" @send="sendVoiceMessage" />

        <!-- Normal Input Row -->
        <div v-else class="flex items-end gap-2 sm:gap-3">
            <!-- Action Bar (Left Side) -->
            <div class="flex items-center gap-1">
                <!-- Voice Recording Button -->
                <button @click="startVoiceRecording" :disabled="!voiceRecorder.isSupported.value" class="action-btn"
                    :class="{ 'opacity-50 cursor-not-allowed': !voiceRecorder.isSupported.value }" title="Ghi âm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z" />
                        <path d="M19 10v2a7 7 0 0 1-14 0v-2" />
                        <line x1="12" x2="12" y1="19" y2="22" />
                    </svg>
                </button>

                <!-- Image Picker Button -->
                <label class="action-btn cursor-pointer" title="Chọn ảnh">
                    <input type="file" accept="image/*" multiple class="hidden" @change="handleImageSelect" />
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                        <circle cx="9" cy="9" r="2" />
                        <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                    </svg>
                </label>

                <!-- Sticker Button (UI Only for now) -->
                <button class="action-btn" title="Nhãn dán">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="M15.5 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-10.5L15.5 3Z" />
                        <path d="M15 3v6h6" />
                        <path d="M10 13h.01" />
                        <path d="M14 13h.01" />
                        <path d="M10 17s1 1 2 1 2-1 2-1" />
                    </svg>
                </button>

                <!-- GIF Button -->
                <div class="relative">
                    <button @click="showGifPicker = !showGifPicker" class="action-btn" title="GIF">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                            <circle cx="9" cy="9" r="2" />
                            <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                        </svg>
                        <span class="gif-badge">GIF</span>
                    </button>

                    <!-- GIF Picker Popup -->
                    <Teleport to="body">
                        <div v-if="showGifPicker" class="picker-overlay" @click="showGifPicker = false">
                            <div class="picker-container" @click.stop
                                :style="{ bottom: '80px', left: '320px', position: 'fixed' }">
                                <GifPicker @select="sendGif" @close="showGifPicker = false" />
                            </div>
                        </div>
                    </Teleport>
                </div>
            </div>

            <!-- Text Input -->
            <div class="flex-1 relative">
                <input ref="inputRef" v-model="message" @keydown.enter.exact.prevent="handleSend" @input="handleTyping"
                    type="text" :placeholder="t('common.chat.type_message')"
                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-full text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-100 transition-all" />
            </div>

            <!-- Emoji Button -->
            <div class="relative">
                <button @click="showEmojiPicker = !showEmojiPicker" class="action-btn" title="Biểu tượng cảm xúc">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M8 14s1.5 2 4 2 4-2 4-2" />
                        <line x1="9" y1="9" x2="9.01" y2="9" />
                        <line x1="15" y1="9" x2="15.01" y2="9" />
                    </svg>
                </button>

                <!-- Emoji Picker Popup -->
                <Teleport to="body">
                    <div v-if="showEmojiPicker" class="emoji-picker-overlay" @click="showEmojiPicker = false">
                        <div class="emoji-picker-container" @click.stop
                            :style="{ bottom: '70px', right: '20px', position: 'fixed' }">
                            <EmojiPicker @select="insertEmoji" @close="showEmojiPicker = false" />
                        </div>
                    </div>
                </Teleport>
            </div>

            <!-- Send Button -->
            <button @click="handleSend" :disabled="disabled || (!message.trim() && attachments.length === 0)"
                class="flex-shrink-0 w-10 h-10 rounded-full bg-teal-500 hover:bg-teal-600 text-white flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z" />
                </svg>
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import type { IMessage } from '../models/Chat'
import EmojiPicker from './EmojiPicker.vue'
import GifPicker from './GifPicker.vue'
import VoiceRecordingUI from './VoiceRecordingUI.vue'
import { useVoiceRecorder } from '../composables/useVoiceRecorder'

const props = defineProps<{
    disabled: boolean
    replyTo?: IMessage | null
}>()

const emit = defineEmits<{
    (e: 'send', content: string, attachments?: File[]): void
    (e: 'typing', isTyping: boolean): void
    (e: 'cancel-reply'): void
}>()

const { t } = useI18n()

// State
const message = ref('')
const attachments = ref<File[]>([])
const inputRef = ref<HTMLInputElement>()
const showEmojiPicker = ref(false)
const showGifPicker = ref(false)

// Voice recorder
const voiceRecorder = useVoiceRecorder()
const isRecording = voiceRecorder.isRecording

let typingTimeout: ReturnType<typeof setTimeout> | null = null

// Methods
function handleSend() {
    const content = message.value.trim()
    if (!content && attachments.value.length === 0) return

    emit('send', content, attachments.value.length > 0 ? attachments.value : undefined)
    message.value = ''
    attachments.value = []
    emit('typing', false)
}

function handleTyping() {
    emit('typing', true)

    if (typingTimeout) clearTimeout(typingTimeout)
    typingTimeout = setTimeout(() => {
        emit('typing', false)
    }, 2000)
}

function handleFileSelect(event: Event) {
    const target = event.target as HTMLInputElement
    if (target.files) {
        attachments.value = [...attachments.value, ...Array.from(target.files)]
        target.value = ''
    }
}

function handleImageSelect(event: Event) {
    const target = event.target as HTMLInputElement
    if (target.files) {
        const imageFiles = Array.from(target.files).filter(f => f.type.startsWith('image/'))
        attachments.value = [...attachments.value, ...imageFiles]
        target.value = ''
    }
}

function removeAttachment(index: number) {
    attachments.value.splice(index, 1)
}

function getPreviewUrl(file: File): string {
    return URL.createObjectURL(file)
}

function insertEmoji(emoji: string) {
    message.value += emoji
    showEmojiPicker.value = false
    inputRef.value?.focus()
}

// Voice recording functions
async function startVoiceRecording() {
    const success = await voiceRecorder.startRecording()
    if (!success && voiceRecorder.error.value) {
        console.error('Recording error:', voiceRecorder.error.value)
        alert(voiceRecorder.error.value)
    }
}

function cancelVoiceRecording() {
    voiceRecorder.cancelRecording()
}

async function sendVoiceMessage() {
    if (voiceRecorder.duration.value < 1) {
        alert('Tin nhắn thoại quá ngắn. Vui lòng ghi ít nhất 1 giây.')
        voiceRecorder.cancelRecording()
        return
    }

    const audioBlob = await voiceRecorder.stopRecording()
    if (audioBlob) {
        const audioFile = new File([audioBlob], 'voice-message.webm', { type: audioBlob.type })
        emit('send', '', [audioFile])
    }
    voiceRecorder.cancelRecording()
}

async function sendGif(url: string) {
    // GIFs are typically sent as a markdown image or just the URL
    // In this app, we treats them as a message with the URL or we could download and send as file
    // For simplicity and matching Meta style, we send as message with URL
    emit('send', url)
    showGifPicker.value = false
}

watch(() => props.replyTo, (val) => {
    if (val && inputRef.value) {
        inputRef.value.focus()
    }
})
</script>

<style scoped>
.action-btn {
    flex-shrink: 0;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #f3f4f6;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6b7280;
    transition: all 0.2s;
    position: relative;
}

.action-btn:hover {
    background: #e5e7eb;
    color: #374151;
}

.action-btn:active {
    transform: scale(0.95);
}

.emoji-picker-overlay,
.picker-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
}

.emoji-picker-container,
.picker-container {
    z-index: 10000;
}

.gif-badge {
    position: absolute;
    bottom: 2px;
    right: 2px;
    background: #14b8a6;
    color: white;
    font-size: 7px;
    font-weight: bold;
    padding: 1px 2px;
    border-radius: 4px;
    line-height: 1;
}
</style>
