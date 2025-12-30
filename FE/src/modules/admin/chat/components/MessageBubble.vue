<template>
    <!-- Individual message row - takes full width -->
    <div class="w-full flex" :class="isMine ? 'justify-end' : 'justify-start'">

        <!-- Received Message Layout -->
        <div v-if="!isMine" class="flex flex-col items-start max-w-[90%] sm:max-w-[75%]">
            <div class="bg-white rounded-2xl rounded-bl-none shadow-sm border border-gray-100 relative group transition-all"
                :class="[
                    (hasImages && !displayContent) ? 'p-0' : 'px-4 py-2.5'
                ]">
                <!-- Group Member Name -->
                <p v-if="message.user && showSenderName" class="text-xs font-bold text-teal-600 mb-1">
                    {{ message.user.name }}
                </p>

                <!-- Multiple Images Grid -->
                <div v-if="hasImages"
                    :class="['grid gap-1 mb-2', imageCount === 1 ? 'grid-cols-1' : (imageCount === 2 ? 'grid-cols-2' : 'grid-cols-2 sm:grid-cols-3')]">
                    <div v-for="att in imageAttachments" :key="att.id"
                        class="relative group/img rounded-lg transition-all"
                        :class="{ 'z-20': activeImageMenuId === att.id }">
                        <img :src="att.url" :alt="att.name"
                            class="w-full h-full min-h-[100px] max-h-[400px] object-cover cursor-pointer hover:brightness-95 transition-all"
                            :class="!displayContent && imageCount === 1 ? 'rounded-none' : ''"
                            @click="$emit('preview-image', att.url)" />

                        <!-- Image Menu Button -->
                        <div class="absolute top-1 right-1 opacity-0 group-hover/img:opacity-100 transition-opacity">
                            <button @click.stop="toggleImageMenu(att.id)"
                                class="p-1.5 rounded bg-black/50 text-white hover:bg-black/70 transition-colors backdrop-blur-sm shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2.5">
                                    <circle cx="12" cy="12" r="1" />
                                    <circle cx="19" cy="12" r="1" />
                                    <circle cx="5" cy="12" r="1" />
                                </svg>
                            </button>

                            <!-- Image Menu -->
                            <div v-if="activeImageMenuId === att.id"
                                class="absolute z-50 mt-1 right-0 bg-white rounded-lg shadow-xl border border-gray-100 py-1 min-w-[140px] text-gray-700 animate-in fade-in zoom-in duration-200">
                                <button @click.stop="downloadImage(att.url, att.name)"
                                    class="w-full flex items-center gap-2 px-3 py-2 text-xs hover:bg-gray-50 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                        <polyline points="7 10 12 15 17 10" />
                                        <line x1="12" y1="15" x2="12" y2="3" />
                                    </svg>
                                    {{ t('common.download') }}
                                </button>
                                <button @click.stop="$emit('reply', message); activeImageMenuId = null"
                                    class="w-full flex items-center gap-2 px-3 py-2 text-xs hover:bg-gray-50 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="9 17 4 12 9 7" />
                                        <path d="M20 18v-2a4 4 0 0 0-4-4H4" />
                                    </svg>
                                    {{ t('common.reply') }}
                                </button>
                                <div class="h-px bg-gray-100 my-1"></div>
                                <button @click.stop="$emit('delete', message.id); activeImageMenuId = null"
                                    class="w-full flex items-center gap-2 px-3 py-2 text-xs text-red-500 hover:bg-red-50 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6" />
                                        <path
                                            d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                    </svg>
                                    {{ t('common.delete') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Audio Attachments -->
                <div v-if="hasAudio" class="flex flex-col gap-2 mb-2 w-full min-w-[200px] sm:min-w-[280px]">
                    <div v-for="att in audioAttachments" :key="att.id" 
                        class="flex flex-col gap-2 p-3 bg-gray-50 rounded-2xl border border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-teal-500 flex items-center justify-center text-white shadow-sm flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z" />
                                    <path d="M19 10v2a7 7 0 0 1-14 0v-2" />
                                    <line x1="12" x2="12" y1="19" y2="22" />
                                </svg>
                            </div>
                            <div class="flex flex-col min-w-0 flex-1">
                                <span class="text-xs font-bold text-gray-700 truncate">{{ att.name }}</span>
                                <span class="text-[10px] text-gray-500">{{ att.size }}</span>
                            </div>
                        </div>
                        <audio controls :src="att.url" class="w-full h-8 custom-audio"></audio>
                    </div>
                </div>

                <!-- Non-image/audio Attachments -->
                <div v-if="hasFiles" class="flex flex-col gap-1 mb-2">
                    <a v-for="att in fileAttachments" :key="att.id" :href="att.url" target="_blank"
                        class="flex items-center gap-2 px-3 py-2 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors border border-gray-100">
                        <div
                            class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-teal-500 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z" />
                                <polyline points="13 2 13 9 20 9" />
                            </svg>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="text-xs font-medium text-gray-700 truncate">{{ att.name }}</span>
                            <span class="text-[10px] text-gray-400">{{ att.size }}</span>
                        </div>
                    </a>
                </div>

                <!-- Text Content -->
                <p v-if="displayContent"
                    class="text-sm text-gray-800 whitespace-pre-wrap break-words leading-relaxed select-text">
                    {{ displayContent }}
                </p>
            </div>

            <!-- Footer: Avatar + Time -->
            <div class="flex items-center gap-2 mt-1.5 px-1">
                <div v-if="showAvatar" class="flex-shrink-0">
                    <img v-if="message.user?.avatar" :src="message.user.avatar"
                        class="w-5 h-5 rounded-full object-cover border border-white shadow-sm" />
                    <div v-else
                        :class="['w-5 h-5 rounded-full flex items-center justify-center text-white text-[9px] font-bold shadow-sm', avatarColor]">
                        {{ initials }}
                    </div>
                </div>
                <span class="text-[10px] text-gray-400 font-medium">{{ formattedTime }}</span>
            </div>
        </div>

        <!-- Sent Message Layout -->
        <div v-else class="flex flex-col items-end max-w-[90%] sm:max-w-[75%]">
            <div class="group relative bg-teal-500 text-white rounded-2xl rounded-br-none shadow-md shadow-teal-500/10 transition-all"
                :class="[
                    (hasImages && !displayContent) ? 'p-0' : 'px-4 py-2.5'
                ]">
                <!-- Delete Action (Hover - Message level) -->
                <button @click="$emit('delete', message.id)"
                    class="absolute -left-3 -top-3 w-6 h-6 bg-white text-red-500 rounded-full shadow-lg border border-red-100 opacity-0 group-hover:opacity-100 hover:bg-red-50 flex items-center justify-center transition-all z-10"
                    :title="t('common.delete')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="3">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </button>

                <!-- Multiple Images Grid -->
                <div v-if="hasImages"
                    :class="['grid gap-1 mb-2', imageCount === 1 ? 'grid-cols-1' : (imageCount === 2 ? 'grid-cols-2' : 'grid-cols-2 sm:grid-cols-3')]">
                    <div v-for="att in imageAttachments" :key="att.id"
                        class="relative group/img rounded-lg transition-all"
                        :class="{ 'z-20': activeImageMenuId === att.id }">
                        <img :src="att.url" :alt="att.name"
                            class="w-full h-full min-h-[100px] max-h-[400px] object-cover cursor-pointer hover:brightness-105 transition-all"
                            :class="!displayContent && imageCount === 1 ? 'rounded-none' : ''"
                            @click="$emit('preview-image', att.url)" />

                        <!-- Image Menu Button -->
                        <div class="absolute top-1 right-1 opacity-0 group-hover/img:opacity-100 transition-opacity">
                            <button @click.stop="toggleImageMenu(att.id)"
                                class="p-1.5 rounded bg-black/50 text-white hover:bg-black/70 transition-colors backdrop-blur-sm shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2.5">
                                    <circle cx="12" cy="12" r="1" />
                                    <circle cx="19" cy="12" r="1" />
                                    <circle cx="5" cy="12" r="1" />
                                </svg>
                            </button>

                            <!-- Image Menu -->
                            <div v-if="activeImageMenuId === att.id"
                                :class="{ 'z-50': activeImageMenuId === att.id, 'z-40': activeImageMenuId !== att.id }"
                                class="absolute mt-1 right-0 bg-white rounded-lg shadow-xl border border-gray-100 py-1 min-w-[140px] text-gray-700 animate-in fade-in zoom-in duration-200 overflow-visible">
                                <button @click.stop="downloadImage(att.url, att.name)"
                                    class="w-full flex items-center gap-2 px-3 py-2 text-xs hover:bg-gray-50 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                        <polyline points="7 10 12 15 17 10" />
                                        <line x1="12" y1="15" x2="12" y2="3" />
                                    </svg>
                                    {{ t('common.download') }}
                                </button>
                                <button @click.stop="$emit('reply', message); activeImageMenuId = null"
                                    class="w-full flex items-center gap-2 px-3 py-2 text-xs hover:bg-gray-50 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="9 17 4 12 9 7" />
                                        <path d="M20 18v-2a4 4 0 0 0-4-4H4" />
                                    </svg>
                                    {{ t('common.reply') }}
                                </button>
                                <div class="h-px bg-gray-100 my-1"></div>
                                <button @click.stop="$emit('delete', message.id); activeImageMenuId = null"
                                    class="w-full flex items-center gap-2 px-3 py-2 text-xs text-red-500 hover:bg-red-50 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6" />
                                        <path
                                            d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                    </svg>
                                    {{ t('common.delete') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Audio Attachments -->
                <div v-if="hasAudio" class="flex flex-col gap-2 mb-2 w-full min-w-[200px] sm:min-w-[280px]">
                    <div v-for="att in audioAttachments" :key="att.id" 
                        class="flex flex-col gap-2 p-3 bg-white/10 rounded-2xl border border-white/10">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-teal-500 shadow-sm flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z" />
                                    <path d="M19 10v2a7 7 0 0 1-14 0v-2" />
                                    <line x1="12" x2="12" y1="19" y2="22" />
                                </svg>
                            </div>
                            <div class="flex flex-col min-w-0 flex-1">
                                <span class="text-xs font-bold text-white truncate">{{ att.name }}</span>
                                <span class="text-[10px] text-white/70">{{ att.size }}</span>
                            </div>
                        </div>
                        <audio controls :src="att.url" class="w-full h-8 custom-audio custom-audio--mine"></audio>
                    </div>
                </div>

                <!-- Non-image/audio Attachments -->
                <div v-if="hasFiles" class="flex flex-col gap-1 mb-2">
                    <a v-for="att in fileAttachments" :key="att.id" :href="att.url" target="_blank"
                        class="flex items-center gap-2 px-3 py-2 bg-white/10 rounded-xl hover:bg-white/20 transition-colors border border-white/10">
                        <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z" />
                                <polyline points="13 2 13 9 20 9" />
                            </svg>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="text-xs font-medium text-white truncate">{{ att.name }}</span>
                            <span class="text-[10px] text-white/70">{{ att.size }}</span>
                        </div>
                    </a>
                </div>

                <!-- Text Content -->
                <p v-if="displayContent"
                    class="text-sm text-white whitespace-pre-wrap break-words leading-relaxed select-text">
                    {{ displayContent }}
                </p>
            </div>

            <!-- Footer: Time + Status -->
            <div class="flex items-center gap-1.5 mt-1.5 px-1">
                <span class="text-[10px] text-gray-400 font-medium">{{ formattedTime }}</span>
                <div class="text-teal-500">
                    <svg v-if="isRead" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="3">
                        <polyline points="20 6 9 17 4 12" />
                        <polyline points="20 12 14 18 11 15" class="-ml-2" />
                    </svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import type { IMessage } from '../models/Chat'

const props = defineProps<{
    message: IMessage
    showAvatar: boolean
    showSenderName?: boolean
    currentUserId?: number
}>()

defineEmits<{
    (e: 'reply', message: IMessage): void
    (e: 'delete', messageId: number): void
    (e: 'preview-image', url: string): void
}>()

const { t } = useI18n()

const avatarColors = ['bg-teal-500', 'bg-blue-500', 'bg-purple-500', 'bg-pink-500', 'bg-orange-500']

// Robust comparison for ID
const isMine = computed(() => {
    const messageUserId = Number(props.message.user_id)
    const currentId = Number(props.currentUserId || 0)
    return currentId !== 0 && messageUserId === currentId
})

const avatarColor = computed(() => avatarColors[Number(props.message.user_id || 0) % avatarColors.length])

const initials = computed(() => {
    const name = props.message.user?.name || '?'
    return name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase()
})

const formattedTime = computed(() => {
    if (!props.message.created_at) return ''
    return new Date(props.message.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
})

const isRead = computed(() => !!props.message.metadata?.read_at)

const displayContent = computed(() => {
    const content = props.message.content
    if (content === null || content === undefined) return ''
    if (typeof content === 'string') return content

    // Robust object handling
    try {
        if (typeof content === 'object') {
            if ('data' in content) return String((content as any).data)
            if ('message' in content) return String((content as any).message)
            if ('text' in content) return String((content as any).text)
            return JSON.stringify(content)
        }
    } catch {
        return 'Invalid content'
    }
    return String(content)
})

const normalizedAttachments = computed(() => {
    if (!props.message.attachments) return []

    return props.message.attachments.map((att: any) => {
        let url = att.url || att.file_path || att.path || ''
        const name = att.name || att.file_name || att.filename || 'File'
        const type = att.type || att.file_type || att.mime_type || ''

        // FIX: Handle URLs from Laravel backend which may have different domains
        // e.g., http://workforce_contract_digitization.io/storage/chat-attachments/2025/12/file.png
        if (url) {
            // If URL contains /storage/, extract the path and use local proxy
            if (url.includes('/storage/')) {
                const storageIndex = url.indexOf('/storage/')
                url = url.substring(storageIndex)
            } else if (!url.startsWith('http') && !url.startsWith('data:') && !url.startsWith('/storage/')) {
                // Handle relative paths like 'chat-attachments/2025/12/file.png'
                const cleanPath = url.replace(/^\//, '')
                url = '/storage/' + encodeURI(cleanPath)
            }
        }

        // Better detection
        const isImage = (type && typeof type === 'string' && type.startsWith('image')) ||
            /\.(jpg|jpeg|png|gif|webp|svg)$/i.test(url) ||
            /\.(jpg|jpeg|png|gif|webp|svg)$/i.test(name) ||
            (url && url.startsWith('data:image/'))
        
        const isAudio = (type && typeof type === 'string' && type.startsWith('audio')) ||
            /\.(mp3|wav|ogg|webm|m4a)$/i.test(url) ||
            /\.(mp3|wav|ogg|webm|m4a)$/i.test(name) ||
            (url && url.startsWith('data:audio/'))

        // Format size
        let sizeText = ''
        if (att.file_size) {
            const size = Number(att.file_size)
            if (size > 1024 * 1024) sizeText = (size / (1024 * 1024)).toFixed(1) + ' MB'
            else sizeText = (size / 1024).toFixed(0) + ' KB'
        }

        return {
            id: att.id || Math.random(),
            url,
            name,
            type,
            isImage,
            isAudio,
            size: sizeText
        }
    })
})

const imageAttachments = computed(() => normalizedAttachments.value.filter(a => a.isImage))
const audioAttachments = computed(() => normalizedAttachments.value.filter(a => a.isAudio))
const fileAttachments = computed(() => normalizedAttachments.value.filter(a => !a.isImage && !a.isAudio))
const hasImages = computed(() => imageAttachments.value.length > 0)
const hasAudio = computed(() => audioAttachments.value.length > 0)
const hasFiles = computed(() => fileAttachments.value.length > 0)
const imageCount = computed(() => imageAttachments.value.length)

// Image menu handling
const activeImageMenuId = ref<number | string | null>(null)

function toggleImageMenu(id: number | string) {
    if (activeImageMenuId.value === id) {
        activeImageMenuId.value = null
    } else {
        activeImageMenuId.value = id
    }
}

function downloadImage(url: string, name: string) {
    const link = document.createElement('a')
    link.href = url
    link.download = name
    link.target = '_blank'
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    activeImageMenuId.value = null
}

// Close menu on click outside
if (typeof window !== 'undefined') {
    window.addEventListener('click', () => {
        activeImageMenuId.value = null
    })
}
</script>
