<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="$emit('close')">
        <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full mx-4 overflow-hidden animate-in zoom-in duration-200">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-900">Đổi chủ đề</h3>
                    <button @click="$emit('close')"
                        class="p-2 rounded-full hover:bg-gray-100 text-gray-400 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18" />
                            <line x1="6" y1="6" x2="18" y2="18" />
                        </svg>
                    </button>
                </div>
                <p class="text-sm text-gray-500 mt-1">Chọn màu sắc cho tin nhắn của bạn</p>
            </div>

            <!-- Theme Grid -->
            <div class="p-6">
                <div class="grid grid-cols-4 gap-3">
                    <button v-for="theme in themes" :key="theme.id" @click="selectTheme(theme.id)" :title="theme.name"
                        class="group relative aspect-square rounded-xl overflow-hidden transition-all hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-2"
                        :class="[
                            selectedThemeId === theme.id
                                ? 'ring-2 ring-offset-2 ring-gray-900 scale-105'
                                : 'hover:ring-2 hover:ring-gray-300'
                        ]">
                        <!-- Theme Preview -->
                        <div class="w-full h-full flex flex-col p-2 bg-gray-50">
                            <!-- Received message preview -->
                            <div :class="['w-3/4 h-2 rounded-full mb-1', theme.receivedBg, 'border border-gray-200']">
                            </div>
                            <!-- Sent message preview -->
                            <div :class="['w-3/4 h-2 rounded-full ml-auto', theme.sentBg]"></div>
                        </div>

                        <!-- Check icon for selected -->
                        <div v-if="selectedThemeId === theme.id"
                            class="absolute inset-0 bg-black/20 flex items-center justify-center">
                            <div class="w-6 h-6 rounded-full bg-white flex items-center justify-center shadow-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="3" class="text-gray-900">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                            </div>
                        </div>

                        <!-- Theme name tooltip -->
                        <div
                            class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/60 to-transparent p-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                            <span class="text-[10px] text-white font-medium">{{ theme.name }}</span>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Preview Section -->
            <div class="px-6 pb-4">
                <p class="text-xs text-gray-400 mb-3 font-medium">Xem trước:</p>
                <div class="bg-gray-50 rounded-xl p-4 space-y-3">
                    <!-- Received message -->
                    <div class="flex justify-start">
                        <div :class="[
                            'px-4 py-2 rounded-2xl rounded-bl-none max-w-[80%] shadow-sm',
                            currentTheme.receivedBg,
                            currentTheme.receivedText,
                            currentTheme.receivedBg === 'bg-white' ? 'border border-gray-100' : ''
                        ]">
                            <p class="text-sm">Xin chào! Bạn khỏe không?</p>
                        </div>
                    </div>
                    <!-- Sent message -->
                    <div class="flex justify-end">
                        <div :class="[
                            'px-4 py-2 rounded-2xl rounded-br-none max-w-[80%] shadow-md',
                            currentTheme.sentBg,
                            currentTheme.sentText,
                            currentTheme.sentShadow
                        ]">
                            <p class="text-sm">Mình khỏe, cảm ơn bạn! 😊</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="px-6 pb-6 flex gap-3">
                <button @click="$emit('close')"
                    class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                    Hủy
                </button>
                <button @click="confirmSelection"
                    class="flex-1 px-4 py-2.5 text-sm font-medium text-white rounded-xl transition-colors"
                    :class="[currentTheme.sentBg.replace('bg-gradient-to-br', 'bg-gradient-to-r')]">
                    Xác nhận
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useChatThemeStore, CHAT_THEMES, type ChatTheme } from '../stores/chatThemeStore'

const props = defineProps<{
    conversationId: number
}>()

const emit = defineEmits<{
    (e: 'close'): void
    (e: 'change', themeId: string): void
}>()

const themeStore = useChatThemeStore()

// Get current theme for this conversation
const currentConversationTheme = themeStore.getTheme(props.conversationId)
const selectedThemeId = ref(currentConversationTheme.id)

const themes = computed(() => CHAT_THEMES)

const currentTheme = computed((): ChatTheme => {
    return CHAT_THEMES.find(t => t.id === selectedThemeId.value) || CHAT_THEMES[0]
})

function selectTheme(themeId: string) {
    selectedThemeId.value = themeId
}

function confirmSelection() {
    themeStore.setTheme(props.conversationId, selectedThemeId.value)
    emit('change', selectedThemeId.value)
    emit('close')
}
</script>
