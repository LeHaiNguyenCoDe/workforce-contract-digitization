<template>
    <div class="notification-bell" ref="bellRef">
        <button class="bell-button" @click="toggleDropdown">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                <path d="M13.73 21a2 2 0 0 1-3.46 0" />
            </svg>
            <span v-if="unreadCount > 0" class="badge">
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
        </button>

        <Transition name="dropdown">
            <div v-if="isOpen" class="dropdown">
                <header class="dropdown-header">
                    <h3>{{ t('common.notifications.title') }}</h3>
                    <button v-if="unreadCount > 0" class="btn-mark-all" @click="handleMarkAllRead">
                        {{ t('common.notifications.mark_all_read') }}
                    </button>
                </header>

                <div class="notification-list" @scroll="handleScroll">
                    <div v-if="isLoading && notifications.length === 0" class="loading">
                        <div class="spinner"></div>
                    </div>

                    <div v-else-if="notifications.length === 0" class="empty">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.5">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                            <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                        </svg>
                        <p>{{ t('common.notifications.empty') }}</p>
                    </div>

                    <template v-else>
                        <div v-for="notification in notifications" :key="notification.id" class="notification-item"
                            :class="{ unread: !notification.read_at }" @click="handleClick(notification)">
                            <div class="icon" :class="getIconClass(notification.type)">
                                <component :is="getIcon(notification.type)" />
                            </div>
                            <div class="content">
                                <p class="text" v-html="formatNotification(notification)"></p>
                                <span class="time">{{ formatTime(notification.created_at) }}</span>
                            </div>
                        </div>

                        <div v-if="isLoading" class="loading-more">
                            <div class="spinner small"></div>
                        </div>
                    </template>
                </div>

                <footer v-if="notifications.length > 0" class="dropdown-footer">
                    <router-link to="/admin/notifications" @click="isOpen = false">
                        {{ t('common.notifications.view_all') }}
                    </router-link>
                </footer>
            </div>
        </Transition>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, h } from 'vue'
import { useI18n } from 'vue-i18n'
import { storeToRefs } from 'pinia'
import { useNotificationStore } from '../stores/notificationStore'
import type { INotification } from '../models/Chat'
import { sanitizeSimpleHtml } from '@/shared/utils/sanitize'

const { t } = useI18n()
const notificationStore = useNotificationStore()
const { notifications, unreadCount, isLoading, hasMore } = storeToRefs(notificationStore)

const isOpen = ref(false)
const bellRef = ref<HTMLElement | null>(null)

function toggleDropdown() {
    isOpen.value = !isOpen.value
    if (isOpen.value && notifications.value.length === 0) {
        notificationStore.fetchNotifications()
    }
}

function handleClickOutside(event: MouseEvent) {
    if (bellRef.value && !bellRef.value.contains(event.target as Node)) {
        isOpen.value = false
    }
}

function handleScroll(event: Event) {
    const target = event.target as HTMLElement
    if (target.scrollHeight - target.scrollTop <= target.clientHeight + 50) {
        if (hasMore.value && !isLoading.value) {
            notificationStore.fetchNotifications(notificationStore.currentPage + 1)
        }
    }
}

async function handleMarkAllRead() {
    await notificationStore.markAllAsRead()
}

async function handleClick(notification: INotification) {
    if (!notification.read_at) {
        await notificationStore.markAsRead(notification.id)
    }

    // Handle navigation based on notification type
    if (notification.type === 'friend_request') {
        // Navigate to friend requests
    } else if (notification.type === 'new_message') {
        // Navigate to conversation
    }

    isOpen.value = false
}

function getIconClass(type: string): string {
    const classes: Record<string, string> = {
        friend_request: 'friend',
        friend_accepted: 'friend',
        new_message: 'message',
        group_invite: 'group'
    }
    return classes[type] || 'default'
}

function getIcon(type: string) {
    const icons: Record<string, any> = {
        friend_request: () => h('svg', {
            xmlns: 'http://www.w3.org/2000/svg', width: 16, height: 16, viewBox: '0 0 24 24',
            fill: 'none', stroke: 'currentColor', 'stroke-width': 2
        }, [
            h('path', { d: 'M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2' }),
            h('circle', { cx: 8.5, cy: 7, r: 4 }),
            h('line', { x1: 20, y1: 8, x2: 20, y2: 14 }),
            h('line', { x1: 23, y1: 11, x2: 17, y2: 11 })
        ]),
        friend_accepted: () => h('svg', {
            xmlns: 'http://www.w3.org/2000/svg', width: 16, height: 16, viewBox: '0 0 24 24',
            fill: 'none', stroke: 'currentColor', 'stroke-width': 2
        }, [
            h('path', { d: 'M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2' }),
            h('circle', { cx: 8.5, cy: 7, r: 4 }),
            h('polyline', { points: '17 11 19 13 23 9' })
        ]),
        new_message: () => h('svg', {
            xmlns: 'http://www.w3.org/2000/svg', width: 16, height: 16, viewBox: '0 0 24 24',
            fill: 'none', stroke: 'currentColor', 'stroke-width': 2
        }, [
            h('path', { d: 'M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z' })
        ]),
        default: () => h('svg', {
            xmlns: 'http://www.w3.org/2000/svg', width: 16, height: 16, viewBox: '0 0 24 24',
            fill: 'none', stroke: 'currentColor', 'stroke-width': 2
        }, [
            h('circle', { cx: 12, cy: 12, r: 10 }),
            h('path', { d: 'M12 16v-4M12 8h.01' })
        ])
    }
    return icons[type] || icons.default
}

function formatNotification(notification: INotification): string {
    const data = notification.data

    let result: string
    switch (notification.type) {
        case 'friend_request':
            result = t('common.notifications.friend_request', { name: `<strong>${data.from_user_name}</strong>` })
            break
        case 'friend_accepted':
            result = t('common.notifications.friend_accepted', { name: `<strong>${data.user_name}</strong>` })
            break
        case 'new_message':
            result = t('common.notifications.new_message', { name: `<strong>${data.sender_name}</strong>` })
            break
        case 'group_invite':
            result = t('common.notifications.group_invite', {
                name: `<strong>${data.inviter_name}</strong>`,
                group: `<strong>${data.group_name}</strong>`
            })
            break
        default:
            result = data.message || t('common.notifications.generic')
    }
    // Sanitize to prevent XSS
    return sanitizeSimpleHtml(result)
}

function formatTime(dateStr: string): string {
    const date = new Date(dateStr)
    const now = new Date()
    const diff = now.getTime() - date.getTime()
    const minutes = Math.floor(diff / 60000)

    if (minutes < 1) return t('common.time.just_now')
    if (minutes < 60) return t('common.time.minutes_ago', { n: minutes })

    const hours = Math.floor(minutes / 60)
    if (hours < 24) return t('common.time.hours_ago', { n: hours })

    const days = Math.floor(hours / 24)
    if (days < 7) return t('common.time.days_ago', { n: days })

    return date.toLocaleDateString()
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
    notificationStore.fetchUnreadCount()
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped lang="scss">
.notification-bell {
    position: relative;
}

.bell-button {
    position: relative;
    width: 40px;
    height: 40px;
    border-radius: 10px;
    border: none;
    background: transparent;
    color: var(--text-secondary, #6b7280);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;

    &:hover {
        background: var(--bg-hover, #f3f4f6);
        color: var(--text-primary, #111827);
    }

    .badge {
        position: absolute;
        top: 4px;
        right: 4px;
        min-width: 18px;
        height: 18px;
        padding: 0 5px;
        border-radius: 9px;
        background: var(--danger, #ef4444);
        color: white;
        font-size: 0.6875rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
    }
}

.dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 8px;
    width: 360px;
    max-height: 480px;
    background: var(--bg-card, #ffffff);
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    z-index: 100;
}

.dropdown-enter-active,
.dropdown-leave-active {
    transition: all 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}

.dropdown-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 16px;
    border-bottom: 1px solid var(--border-color, #e5e7eb);

    h3 {
        font-size: 1rem;
        font-weight: 600;
        margin: 0;
    }

    .btn-mark-all {
        padding: 0;
        border: none;
        background: transparent;
        font-size: 0.8125rem;
        color: var(--primary, #3b82f6);
        cursor: pointer;

        &:hover {
            text-decoration: underline;
        }
    }
}

.notification-list {
    flex: 1;
    overflow-y: auto;
    max-height: 360px;
}

.notification-item {
    display: flex;
    gap: 12px;
    padding: 12px 16px;
    cursor: pointer;
    transition: background 0.2s;

    &:hover {
        background: var(--bg-hover, #f3f4f6);
    }

    &.unread {
        background: var(--primary-light, #eff6ff);

        &:hover {
            background: #dbeafe;
        }
    }
}

.icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;

    &.friend {
        background: #dbeafe;
        color: #3b82f6;
    }

    &.message {
        background: #d1fae5;
        color: #10b981;
    }

    &.group {
        background: #fef3c7;
        color: #f59e0b;
    }

    &.default {
        background: var(--bg-muted, #f3f4f6);
        color: var(--text-secondary, #6b7280);
    }
}

.content {
    flex: 1;
    min-width: 0;

    .text {
        font-size: 0.875rem;
        line-height: 1.4;
        margin: 0 0 4px 0;
        color: var(--text-primary, #111827);

        :deep(strong) {
            font-weight: 600;
        }
    }

    .time {
        font-size: 0.75rem;
        color: var(--text-muted, #9ca3af);
    }
}

.loading,
.empty {
    padding: 32px 16px;
    text-align: center;
}

.loading .spinner {
    width: 32px;
    height: 32px;
    margin: 0 auto;
    border: 3px solid var(--border-color, #e5e7eb);
    border-top-color: var(--primary, #3b82f6);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

.loading-more {
    padding: 12px;
    text-align: center;

    .spinner.small {
        width: 20px;
        height: 20px;
        border-width: 2px;
        margin: 0 auto;
        border: 2px solid var(--border-color, #e5e7eb);
        border-top-color: var(--primary, #3b82f6);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.empty {
    color: var(--text-muted, #9ca3af);

    svg {
        margin-bottom: 12px;
        opacity: 0.5;
    }

    p {
        margin: 0;
        font-size: 0.875rem;
    }
}

.dropdown-footer {
    padding: 12px 16px;
    border-top: 1px solid var(--border-color, #e5e7eb);
    text-align: center;

    a {
        font-size: 0.875rem;
        color: var(--primary, #3b82f6);
        text-decoration: none;

        &:hover {
            text-decoration: underline;
        }
    }
}
</style>
