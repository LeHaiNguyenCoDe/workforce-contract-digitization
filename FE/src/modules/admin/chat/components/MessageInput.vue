<template>
    <div class="bg-white border-t border-gray-100 p-3 sm:p-4 flex-shrink-0">
        <!-- Reply Preview -->
        <div v-if="replyTo"
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
        <div v-if="attachments.length > 0" class="mb-3 flex flex-wrap gap-2">
            <div v-for="(file, index) in attachments" :key="index" class="relative group">
                <img v-if="file.type.startsWith('image')" :src="getPreviewUrl(file)"
                    class="w-16 h-16 sm:w-20 sm:h-20 object-cover rounded-lg border border-gray-200" />
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

        <!-- Input Row -->
        <div class="flex items-end gap-2 sm:gap-3">
            <!-- Emoji Button -->
            <button
                class="flex-shrink-0 w-10 h-10 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 hover:text-gray-700 flex items-center justify-center transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M8 14s1.5 2 4 2 4-2 4-2" />
                    <line x1="9" y1="9" x2="9.01" y2="9" />
                    <line x1="15" y1="9" x2="15.01" y2="9" />
                </svg>
            </button>

            <!-- Text Input -->
            <div class="flex-1 relative">
                <input ref="inputRef" v-model="message" @keydown.enter.exact.prevent="handleSend" @input="handleTyping"
                    type="text" :placeholder="t('common.chat.type_message')"
                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-full text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-100 transition-all" />
            </div>

            <!-- Attach Button -->
            <label
                class="flex-shrink-0 w-10 h-10 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 hover:text-gray-700 flex items-center justify-center cursor-pointer transition-all">
                <input type="file" multiple class="hidden" @change="handleFileSelect" />
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2">
                    <path
                        d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48" />
                </svg>
            </label>

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

const message = ref('')
const attachments = ref<File[]>([])
const inputRef = ref<HTMLInputElement>()

let typingTimeout: ReturnType<typeof setTimeout> | null = null

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

function removeAttachment(index: number) {
    attachments.value.splice(index, 1)
}

function getPreviewUrl(file: File): string {
    return URL.createObjectURL(file)
}

watch(() => props.replyTo, (val) => {
    if (val && inputRef.value) {
        inputRef.value.focus()
    }
})
</script>
