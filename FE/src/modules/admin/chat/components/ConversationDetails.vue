<template>
    <div
        class="h-full flex flex-col bg-white border-l border-gray-100 w-full sm:w-80 flex-shrink-0 animate-in slide-in-from-right duration-300 overflow-y-auto fixed sm:relative inset-0 sm:inset-auto z-50 sm:z-auto">
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

        <!-- Search Panel (conditionally shown) -->
        <div v-if="isSearchOpen" class="flex-1 flex flex-col">
            <div class="p-4 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <button @click="closeSearch" class="p-2 rounded-lg hover:bg-gray-100 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <polyline points="15 18 9 12 15 6" />
                        </svg>
                    </button>
                    <div class="flex-1 relative">
                        <input v-model="searchQuery" @input="debouncedSearch" type="text"
                            :placeholder="t('common.chat.details.search_messages')"
                            class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-teal-400" />
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8" />
                            <path d="m21 21-4.35-4.35" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="flex-1 overflow-y-auto p-4">
                <div v-if="isSearching" class="flex justify-center py-8">
                    <div class="w-6 h-6 border-2 border-gray-200 border-t-teal-500 rounded-full animate-spin"></div>
                </div>
                <div v-else-if="searchResults.length === 0 && searchQuery.length > 0" class="text-center py-8">
                    <p class="text-sm text-gray-400">{{ t('common.noData') }}</p>
                </div>
                <div v-else class="space-y-2">
                    <button v-for="msg in searchResults" :key="msg.id" @click="scrollToMessage(msg.id)"
                        class="w-full text-left p-3 rounded-lg hover:bg-gray-50 transition-colors">
                        <p class="text-xs text-gray-400 mb-1">{{ formatMessageDate(msg.created_at) }}</p>
                        <p class="text-sm text-gray-700 line-clamp-2">{{ msg.content }}</p>
                    </button>
                </div>
            </div>
        </div>

        <!-- Media Gallery Panel -->
        <div v-else-if="isMediaGalleryOpen" class="flex-1 flex flex-col">
            <div class="p-4 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <button @click="closeMediaGallery" class="p-2 rounded-lg hover:bg-gray-100 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <polyline points="15 18 9 12 15 6" />
                        </svg>
                    </button>
                    <h3 class="font-semibold text-gray-800">{{ t('common.chat.details.media') }}</h3>
                </div>
            </div>
            <div class="flex-1 overflow-y-auto p-4">
                <div v-if="isLoadingMedia" class="flex justify-center py-8">
                    <div class="w-6 h-6 border-2 border-gray-200 border-t-teal-500 rounded-full animate-spin"></div>
                </div>
                <div v-else-if="sharedMedia.length === 0" class="text-center py-8">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mx-auto mb-3 text-gray-200"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                        <circle cx="8.5" cy="8.5" r="1.5" />
                        <path d="m21 15-5-5L5 21" />
                    </svg>
                    <p class="text-sm text-gray-400">{{ t('common.noData') }}</p>
                </div>
                <div v-else class="grid grid-cols-3 gap-2">
                    <button v-for="media in sharedMedia" :key="media.id" @click="openMediaPreview(media)"
                        class="aspect-square rounded-lg overflow-hidden hover:opacity-80 transition-opacity">
                        <img :src="media.thumbnail || media.url" :alt="media.name"
                            class="w-full h-full object-cover" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Files List Panel -->
        <div v-else-if="isFilesListOpen" class="flex-1 flex flex-col">
            <div class="p-4 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <button @click="closeFilesList" class="p-2 rounded-lg hover:bg-gray-100 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <polyline points="15 18 9 12 15 6" />
                        </svg>
                    </button>
                    <h3 class="font-semibold text-gray-800">{{ t('common.chat.details.files') }}</h3>
                </div>
            </div>
            <div class="flex-1 overflow-y-auto p-4">
                <div v-if="isLoadingMedia" class="flex justify-center py-8">
                    <div class="w-6 h-6 border-2 border-gray-200 border-t-teal-500 rounded-full animate-spin"></div>
                </div>
                <div v-else-if="sharedFiles.length === 0" class="text-center py-8">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mx-auto mb-3 text-gray-200"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                    </svg>
                    <p class="text-sm text-gray-400">{{ t('common.noData') }}</p>
                </div>
                <div v-else class="space-y-2">
                    <a v-for="file in sharedFiles" :key="file.id" :href="file.url" target="_blank"
                        class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                <polyline points="14 2 14 8 20 8" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-700 truncate">{{ file.name }}</p>
                            <p class="text-xs text-gray-400">{{ formatFileSize(file.size) }}</p>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" class="text-gray-400">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="7 10 12 15 17 10" />
                            <line x1="12" y1="15" x2="12" y2="3" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Default Profile View -->
        <template v-else>
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
                    <span class="text-[10px] font-medium text-gray-500 uppercase tracking-wider">{{ t('common.chat.details.end_to_end_encryption') }}</span>
                </div>

                <!-- Quick Actions -->
                <div class="flex justify-center gap-6 mt-6 w-full">
                    <button @click="handleViewProfile" class="flex flex-col items-center gap-1.5 group">
                        <div
                            class="w-9 h-9 flex items-center justify-center rounded-full bg-gray-100 text-gray-600 group-hover:bg-gray-200 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                        </div>
                        <span class="text-[10px] font-medium text-gray-500">{{ t('common.chat.details.view_profile')
                            }}</span>
                    </button>
                    <button @click="openSearch" class="flex flex-col items-center gap-1.5 group">
                        <div
                            class="w-9 h-9 flex items-center justify-center rounded-full bg-gray-100 text-gray-600 group-hover:bg-gray-200 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8" />
                                <path d="m21 21-4.35-4.35" />
                            </svg>
                        </div>
                        <span class="text-[10px] font-medium text-gray-500">{{ t('common.chat.details.search_messages')
                            }}</span>
                    </button>
                    <button @click="handleTogglePin" class="flex flex-col items-center gap-1.5 group">
                        <div :class="[
                            'w-9 h-9 flex items-center justify-center rounded-full transition-colors',
                            isPinned
                                ? 'bg-indigo-100 text-indigo-600 group-hover:bg-indigo-200'
                                : 'bg-gray-100 text-gray-600 group-hover:bg-gray-200'
                        ]">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z" />
                                <path v-if="isPinned" d="M12 7v10" stroke-width="3" />
                            </svg>
                        </div>
                        <span class="text-[10px] font-medium text-gray-500">
                            {{ isPinned ? t('common.chat.details.pinned') : t('common.chat.details.pin_conversation') }}
                        </span>
                    </button>
                </div>
            </div>

            <!-- Options Sections -->
            <div class="flex-1">
                <CollapsibleSection :title="t('common.chat.details.info')" icon="info">
                    <button @click="handleViewPinnedMessages"
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
                        <button @click="handleChangeTheme"
                            class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <div class="w-4 h-4 rounded-full bg-primary/20 flex items-center justify-center">
                                <div class="w-2 h-2 rounded-full bg-primary animate-pulse"></div>
                            </div>
                            {{ t('common.chat.details.change_theme') }}
                        </button>
                        <button @click="handleChangeEmoji"
                            class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <span class="text-lg leading-none">🦊</span>
                            {{ t('common.chat.details.change_emoji') }}
                        </button>
                        <button v-if="conversation.type === 'group'" @click="handleEditNicknames"
                            class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors font-medium">
                            <span class="text-xs font-bold text-gray-400 w-4 text-center">Aa</span>
                            {{ t('common.chat.details.edit_nicknames') }}
                        </button>
                    </div>
                </CollapsibleSection>

                <CollapsibleSection :title="t('common.chat.details.media_files')" icon="image">
                    <div class="space-y-1">
                        <button @click="openMediaGallery"
                            class="w-full flex items-center justify-between px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                                    <circle cx="8.5" cy="8.5" r="1.5" />
                                    <path d="m21 15-5-5L5 21" />
                                </svg>
                                {{ t('common.chat.details.media') }}
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400">
                                <polyline points="9 18 15 12 9 6" />
                            </svg>
                        </button>
                        <button @click="openFilesList"
                            class="w-full flex items-center justify-between px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                    <polyline points="14 2 14 8 20 8" />
                                </svg>
                                {{ t('common.chat.details.files') }}
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400">
                                <polyline points="9 18 15 12 9 6" />
                            </svg>
                        </button>
                    </div>
                </CollapsibleSection>

                <CollapsibleSection :title="t('common.chat.details.privacy_support')" icon="shield">
                    <div class="space-y-1">
                        <button @click="handleToggleMute"
                            class="w-full flex items-center justify-between px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400">
                                    <path d="M18 8a6 6 0 0 0-12 0c0 7 3 9 3 9h6s3-2 3-9" />
                                </svg>
                                {{ t('common.chat.details.mute_notifications') }}
                            </div>
                             <span :class="['text-xs font-medium', isMuted ? 'text-amber-500' : 'text-gray-400']">
                                {{ isMuted ? 'Đang tắt' : 'Đang bật' }}
                            </span>
                        </button>
                        <button @click="handleTogglePin"
                            class="w-full flex items-center justify-between px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400">
                                    <path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z" />
                                </svg>
                                Ghim cuộc trò chuyện
                            </div>
                            <span :class="['text-xs font-medium', isPinned ? 'text-indigo-500' : 'text-gray-400']">
                                {{ isPinned ? 'Đã ghim' : 'Chưa ghim' }}
                            </span>
                        </button>

                        <!-- Messaging Permissions -->
                        <button v-if="conversation?.type === 'group'"
                            class="w-full flex items-center justify-between px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400">
                                    <circle cx="12" cy="12" r="10" />
                                    <line x1="12" y1="8" x2="12" y2="12" />
                                    <line x1="12" y1="16" x2="12.01" y2="16" />
                                </svg>
                                {{ t('common.chat.details.messaging_permissions') }}
                            </div>
                            <span class="text-xs text-gray-400">
                                {{ messagingPermissions === 'all' ? t('common.chat.details.all_members') : t('common.chat.details.admin_only') }}
                            </span>
                        </button>

                        <!-- Disappearing Messages -->
                        <button
                            class="w-full flex items-center justify-between px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400">
                                    <circle cx="12" cy="12" r="10" />
                                    <polyline points="12 6 12 12 16 14" />
                                </svg>
                                {{ t('common.chat.details.disappearing_messages') }}
                            </div>
                            <span class="text-xs text-gray-400">
                                {{ disappearingMessages ? (disappearingMessages / 3600) + 'h' : t('common.off') }}
                            </span>
                        </button>

                        <!-- Read Receipts -->
                        <button @click="handleToggleReadReceipts"
                            class="w-full flex items-center justify-between px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                    <polyline points="22 4 12 14.01 9 11.01" />
                                </svg>
                                {{ t('common.chat.details.read_receipts') }}
                            </div>
                            <span :class="['text-xs font-medium', readReceipts ? 'text-green-500' : 'text-gray-400']">
                                {{ readReceipts ? t('common.on') : t('common.off') }}
                            </span>
                        </button>

                        <!-- Encryption Placeholder -->
                        <button
                            class="w-full flex items-center justify-between px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                </svg>
                                {{ t('common.chat.details.end_to_end_encryption') }}
                            </div>
                        </button>
                        <button
                            class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400">
                                <circle cx="12" cy="12" r="10" />
                                <path d="m4.93 4.93 14.14 14.14" />
                            </svg>
                            {{ t('common.chat.details.messaging_permissions') }}
                        </button>
                        <button
                            class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 16 14" />
                            </svg>
                            {{ t('common.chat.details.disappearing_messages') }}
                        </button>
                        <button
                            class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400">
                                <circle cx="12" cy="12" r="10" />
                                <path d="m9 12 2 2 4-4" />
                            </svg>
                            {{ t('common.chat.details.read_receipts') }}
                            <span class="ml-auto text-xs text-slate-400">Bật</span>
                        </button>
                        <button
                            class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors font-medium">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400">
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
                    <button v-if="friendshipStatus === 'blocked_by_me' || friendshipStatus === 'blocked_mutually'" @click="handleUnblock"
                        class="w-full flex items-center gap-3 px-4 py-2 text-sm text-teal-600 hover:bg-teal-50 transition-colors font-medium">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <path d="m9 12 2 2 4-4" />
                        </svg>
                        {{ t('common.chat.details.unblock') }}
                    </button>
                    <button v-else-if="friendshipStatus !== 'blocked_by_them'" @click="handleBlock"
                        class="w-full flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors font-medium">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <path d="m4.93 4.93 14.14 14.14" />
                        </svg>
                        {{ t('common.chat.details.block') }}
                    </button>
                    <button @click="handleReport"
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
        </template>

        <!-- Block Confirmation Modal -->
        <div v-if="showBlockModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
            @click.self="showBlockModal = false">
            <div class="bg-white rounded-xl shadow-xl p-6 max-w-sm mx-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ t('common.chat.details.block') }}</h3>
                <p class="text-sm text-gray-600 mb-4">Bạn có chắc chắn muốn chặn người dùng này? Bạn sẽ không thể gửi tin nhắn cho họ và họ cũng không thể nhắn tin cho bạn.</p>
                <div class="flex gap-3">
                    <button @click="showBlockModal = false"
                        class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        {{ t('common.cancel') }}
                    </button>
                    <button @click="confirmBlock"
                        class="flex-1 px-4 py-2 text-sm font-medium text-white bg-red-500 hover:bg-red-600 rounded-lg transition-colors">
                        {{ t('common.chat.details.block') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Report Modal -->
        <div v-if="showReportModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
            @click.self="showReportModal = false">
            <div class="bg-white rounded-xl shadow-xl p-6 max-w-sm mx-4 w-full">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ t('common.chat.details.report') }}</h3>
                <p class="text-sm text-gray-600 mb-4">Vui lòng cho chúng tôi biết lý do báo cáo:</p>
                <textarea v-model="reportReason" rows="3"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-teal-400 resize-none mb-4"
                    placeholder="Nhập lý do..."></textarea>
                <div class="flex gap-3">
                    <button @click="showReportModal = false"
                        class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        {{ t('common.cancel') }}
                    </button>
                    <button @click="confirmReport" :disabled="!reportReason.trim()"
                        class="flex-1 px-4 py-2 text-sm font-medium text-white bg-red-500 hover:bg-red-600 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        {{ t('common.submit') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Theme Picker Modal -->
        <ThemePicker 
            v-if="showThemePicker" 
            :conversationId="conversation.id"
            @close="showThemePicker = false"
            @change="showThemePicker = false" 
        />
    </div>
</template>

<script setup lang="ts">
import { ref, toRef, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import type { IConversation } from '../models/Chat'
import CollapsibleSection from './CollapsibleSection.vue'
import ThemePicker from './ThemePicker.vue'
import { useConversationInfo, useConversationDetails } from '../composables'

const props = defineProps<{
    conversation: IConversation
}>()

const emit = defineEmits<{
    (e: 'close'): void
    (e: 'scroll-to-message', messageId: number): void
}>()

const { t } = useI18n()

// Use shared conversation info composable - eliminates duplicate code
const conversationRef = toRef(props, 'conversation')
const {
    name,
    avatar,
    initials,
    avatarColor,
    status
} = useConversationInfo(conversationRef as any)

// Use conversation details composable for functionality
const {
    isMuted,
    isPinned,
    readReceipts,
    messagingPermissions,
    disappearingMessages,
    isSearchOpen,
    isMediaGalleryOpen,
    isFilesListOpen,
    searchQuery,
    searchResults,
    isSearching,
    sharedMedia,
    sharedFiles,
    isLoadingMedia,
    toggleMute,
    togglePin,
    toggleReadReceipts,
    updateMessagingPermissions,
    updateDisappearingMessages,
    searchMessages,
    openSearch,
    closeSearch,
    openMediaGallery,
    closeMediaGallery,
    openFilesList,
    closeFilesList,
    friendshipStatus,
    blockUser,
    unblockUser,
    reportConversation
} = useConversationDetails(conversationRef as any)

// Local state for modals
const showBlockModal = ref(false)
const showReportModal = ref(false)
const showThemePicker = ref(false)
const reportReason = ref('')

// Debounced search
let searchTimeout: ReturnType<typeof setTimeout> | null = null
function debouncedSearch() {
    if (searchTimeout) clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        if (searchQuery.value.length >= 2) {
            searchMessages(searchQuery.value)
        }
    }, 300)
}

// Format message date
function formatMessageDate(dateStr: string): string {
    const date = new Date(dateStr)
    return date.toLocaleDateString([], {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

// Format file size
function formatFileSize(bytes: number): string {
    if (bytes < 1024) return bytes + ' B'
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB'
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB'
}

// Scroll to message in chat window
function scrollToMessage(messageId: number) {
    emit('scroll-to-message', messageId)
    closeSearch()
}

// Open media preview (could use a lightbox library)
function openMediaPreview(media: any) {
    window.open(media.url, '_blank')
}

// Action handlers
function handleViewProfile() {
    // Could navigate to user profile or open a modal
    // For now, this is a placeholder
}

async function handleToggleMute() {
    try {
        await toggleMute()
    } catch (error) {
        // Failed to toggle mute
    }
}

async function handleTogglePin() {
    try {
        await togglePin()
    } catch (error) {
        // Failed to toggle pin
    }
}

async function handleToggleReadReceipts() {
    try {
        await toggleReadReceipts()
    } catch (error) {
        // Failed to toggle read receipts
    }
}

function handleViewPinnedMessages() {
    // Could open a pinned messages panel
}

function handleChangeTheme() {
    showThemePicker.value = true
}

function handleChangeEmoji() {
    // Could open an emoji picker for quick reactions
}

function handleEditNicknames() {
    // Could open a nickname editor for group members
}

function handleBlock() {
    showBlockModal.value = true
}

async function confirmBlock() {
    try {
        await blockUser()
        showBlockModal.value = false
    } catch (error) {
        // Failed to block user
    }
}

async function handleUnblock() {
    try {
        await unblockUser()
    } catch (error) {
        // Failed to unblock user
    }
}

function handleReport() {
    showReportModal.value = true
    reportReason.value = ''
}

async function confirmReport() {
    if (!reportReason.value.trim()) return
    try {
        await reportConversation(reportReason.value)
        showReportModal.value = false
        reportReason.value = ''
    } catch (error) {
        // Failed to report conversation
    }
}

// Reset modals when conversation changes
watch(() => props.conversation?.id, () => {
    showBlockModal.value = false
    showReportModal.value = false
    reportReason.value = ''
})
</script>
