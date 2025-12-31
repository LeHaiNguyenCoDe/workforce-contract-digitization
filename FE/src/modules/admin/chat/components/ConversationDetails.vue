<template>
    <div
        class="h-full flex flex-col bg-white border-l border-gray-100 w-80 flex-shrink-0 animate-in slide-in-from-right duration-300 overflow-y-auto">
        <!-- Close button for mobile -->
        <div class="p-4 border-b border-gray-100 md:hidden flex justify-between items-center">
            <h3 class="font-semibold text-gray-800">{{ t('common.chat.details.title') }}</h3>
            <button @click="$emit('close')" class="p-2 rounded-lg hover:bg-gray-100 text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
            </button>
        </div>

        <!-- Profile Section -->
        <div class="p-6 flex flex-col items-center text-center">
            <div class="relative mb-3">
                <img v-if="avatar" :src="avatar" :alt="name" class="w-20 h-20 rounded-full object-cover shadow-sm" />
                <div v-else
                    :class="['w-20 h-20 rounded-full flex items-center justify-center text-white text-xl font-bold shadow-sm', avatarColor]">
                    {{ initials }}
                </div>
            </div>
            <h3 class="text-lg font-bold text-gray-900">{{ name }}</h3>
            <p class="text-xs text-gray-400 mt-1">{{ status }}</p>

            <div class="flex items-center gap-2 mt-2 px-3 py-1 bg-gray-50 rounded-full border border-gray-100">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" class="text-gray-400">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                </svg>
                <span class="text-[10px] font-medium text-gray-500 uppercase tracking-wider">Được mã hóa đầu cuối</span>
            </div>

            <!-- Quick Actions -->
            <div class="flex justify-center gap-6 mt-6 w-full">
                <button class="flex flex-col items-center gap-1.5 group">
                    <div
                        class="w-9 h-9 flex items-center justify-center rounded-full bg-gray-100 text-gray-600 group-hover:bg-gray-200 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                    </div>
                    <span class="text-[10px] font-medium text-gray-500">{{ t('common.chat.details.view_profile')
                        }}</span>
                </button>
                <button class="flex flex-col items-center gap-1.5 group">
                    <div
                        class="w-9 h-9 flex items-center justify-center rounded-full bg-gray-100 text-gray-600 group-hover:bg-gray-200 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M18 8a6 6 0 0 0-12 0c0 7 3 9 3 9h6s3-2 3-9" />
                            <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                        </svg>
                    </div>
                    <span class="text-[10px] font-medium text-gray-500">{{ t('common.chat.details.mute_notifications')
                        }}</span>
                </button>
                <button class="flex flex-col items-center gap-1.5 group">
                    <div
                        class="w-9 h-9 flex items-center justify-center rounded-full bg-gray-100 text-gray-600 group-hover:bg-gray-200 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8" />
                            <path d="m21 21-4.35-4.35" />
                        </svg>
                    </div>
                    <span class="text-[10px] font-medium text-gray-500">{{ t('common.chat.details.search_messages')
                        }}</span>
                </button>
            </div>
        </div>

        <!-- Options Sections -->
        <div class="flex-1">
            <CollapsibleSection :title="t('common.chat.details.info')" icon="info">
                <button
                    class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" class="text-gray-400">
                        <path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z" />
                    </svg>
                    {{ t('common.chat.details.pinned_messages') }}
                </button>
            </CollapsibleSection>

            <CollapsibleSection :title="t('common.chat.details.customization')" icon="edit">
                <div class="space-y-1">
                    <button
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <div class="w-4 h-4 rounded-full bg-primary/20 flex items-center justify-center">
                            <div class="w-2 h-2 rounded-full bg-primary animate-pulse"></div>
                        </div>
                        {{ t('common.chat.details.change_theme') }}
                    </button>
                    <button
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <span class="text-lg leading-none">🦊</span>
                        {{ t('common.chat.details.change_emoji') }}
                    </button>
                    <button
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors font-medium">
                        <span class="text-xs font-bold text-gray-400 w-4 text-center">Aa</span>
                        {{ t('common.chat.details.edit_nicknames') }}
                    </button>
                </div>
            </CollapsibleSection>

            <CollapsibleSection :title="t('common.chat.details.media_files')" icon="image">
                <div class="space-y-1">
                    <button
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" class="text-gray-400">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                            <circle cx="8.5" cy="8.5" r="1.5" />
                            <path d="m21 15-5-5L5 21" />
                        </svg>
                        {{ t('common.chat.details.media') }}
                    </button>
                    <button
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" class="text-gray-400">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                            <polyline points="14 2 14 8 20 8" />
                        </svg>
                        {{ t('common.chat.details.files') }}
                    </button>
                </div>
            </CollapsibleSection>

            <CollapsibleSection :title="t('common.chat.details.privacy_support')" icon="shield">
                <div class="space-y-1">
                    <button
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" class="text-gray-400">
                            <path d="M18 8a6 6 0 0 0-12 0c0 7 3 9 3 9h6s3-2 3-9" />
                        </svg>
                        {{ t('common.chat.details.mute_notifications') }}
                    </button>
                    <button
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" class="text-gray-400">
                            <circle cx="12" cy="12" r="10" />
                            <path d="m4.93 4.93 14.14 14.14" />
                        </svg>
                        {{ t('common.chat.details.messaging_permissions') }}
                    </button>
                    <button
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" class="text-gray-400">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                        {{ t('common.chat.details.disappearing_messages') }}
                    </button>
                    <button
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" class="text-gray-400">
                            <circle cx="12" cy="12" r="10" />
                            <path d="m9 12 2 2 4-4" />
                        </svg>
                        {{ t('common.chat.details.read_receipts') }}
                        <span class="ml-auto text-xs text-slate-400">Bật</span>
                    </button>
                    <button
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors font-medium">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" class="text-gray-400">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                        {{ t('common.chat.details.end_to_end_encryption') }}
                    </button>
                </div>
            </CollapsibleSection>

            <div class="mt-4 pb-10">
                <button
                    class="w-full flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" class="text-gray-400">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 8v4" />
                        <path d="M12 16h.01" />
                    </svg>
                    {{ t('common.chat.details.restrict') }}
                </button>
                <button
                    class="w-full flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10" />
                        <path d="m4.93 4.93 14.14 14.14" />
                    </svg>
                    {{ t('common.chat.details.block') }}
                </button>
                <button
                    class="w-full flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" class="text-gray-400">
                        <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z" />
                        <line x1="4" y1="22" x2="4" y2="15" />
                    </svg>
                    <div class="flex flex-col items-start leading-tight">
                        <span class="font-medium">{{ t('common.chat.details.report') }}</span>
                        <span class="text-[10px] text-slate-400">Đóng góp ý kiến và báo cáo cuộc trò chuyện</span>
                    </div>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import type { IConversation } from '../models/Chat'
import CollapsibleSection from './CollapsibleSection.vue'

const props = defineProps<{
    conversation: IConversation
}>()

defineEmits<{
    (e: 'close'): void
}>()

const { t } = useI18n()

const name = computed(() => {
    if (props.conversation.name) return props.conversation.name
    if (props.conversation.type === 'private' && props.conversation.users?.length === 1) {
        return props.conversation.users[0].name
    }
    return t('common.chat.no_name')
})

const avatar = computed(() => {
    if (props.conversation.avatar) return props.conversation.avatar
    if (props.conversation.type === 'private' && props.conversation.users?.length === 1) {
        return props.conversation.users[0].avatar || null
    }
    return null
})

const status = computed(() => {
    if (props.conversation.type === 'private' && props.conversation.users?.length === 1) {
        return props.conversation.users[0].is_online ? t('common.chat.online') : t('common.chat.offline')
    }
    if (props.conversation.type === 'group') {
        return `${props.conversation.users?.length || 0} ${t('common.chat.members')}`
    }
    return ''
})

const initials = computed(() => {
    return name.value.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase()
})

const avatarColors = ['bg-teal-500', 'bg-blue-500', 'bg-purple-500', 'bg-pink-500', 'bg-orange-500']
const avatarColor = computed(() => {
    return avatarColors[props.conversation.id % avatarColors.length]
})
</script>
