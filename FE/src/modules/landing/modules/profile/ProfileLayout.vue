<script setup lang="ts">
import { RouterView, RouterLink, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores'

const route = useRoute()
const authStore = useAuthStore()

const tabs = [
    { path: '/profile/cart', icon: '🛒', label: 'Giỏ hàng của tôi' },
    { path: '/profile/info', icon: '👤', label: 'Thông tin của tôi' },
    { path: '/profile/address', icon: '📍', label: 'Địa chỉ nhận hàng' },
    { path: '/profile/payment', icon: '💳', label: 'Phương thức thanh toán' }
]

const isActiveTab = (path: string) => {
    return route.path === path || route.path.startsWith(path + '/')
}

const services = [
    { icon: '🕐', label: 'Phục vụ 24/24', desc: 'Xem thêm' },
    { icon: '🛒', label: 'Mua sắm online', desc: 'Xem thêm' },
    { icon: '💳', label: 'Thanh toán online', desc: 'Xem thêm' },
    { icon: '🚚', label: 'Vận chuyển free', desc: 'Xem thêm' }
]
</script>

<template>
    <div class="profile-layout">
        <div class="container">
            <!-- Breadcrumb -->
            <nav class="breadcrumb">
                <RouterLink to="/">Home</RouterLink>
                <span>/</span>
                <span>Tài khoản</span>
                <span>/</span>
                <span class="current">{{tabs.find(t => isActiveTab(t.path))?.label || 'Tài khoản'}}</span>
            </nav>

            <!-- Tab Navigation -->
            <div class="tab-nav">
                <RouterLink v-for="tab in tabs" :key="tab.path" :to="tab.path" class="tab-item"
                    :class="{ active: isActiveTab(tab.path) }">
                    <span class="tab-icon">{{ tab.icon }}</span>
                    <span class="tab-label">{{ tab.label }}</span>
                </RouterLink>
            </div>

            <!-- Content -->
            <div class="profile-content">
                <RouterView />
            </div>

            <!-- Services Footer -->
            <div class="services-bar">
                <div v-for="service in services" :key="service.label" class="service-item">
                    <span class="service-icon">{{ service.icon }}</span>
                    <div class="service-info">
                        <span class="service-label">{{ service.label }}</span>
                        <span class="service-desc">{{ service.desc }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.profile-layout {
    min-height: 100vh;
    padding: var(--space-6) 0;
}

.breadcrumb {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    font-size: var(--text-sm);
    color: var(--color-text-secondary);
    margin-bottom: var(--space-6);
}

.breadcrumb a {
    color: var(--color-text-secondary);
    text-decoration: none;
}

.breadcrumb a:hover {
    color: var(--color-primary);
}

.breadcrumb .current {
    color: var(--color-text-primary);
}

.tab-nav {
    display: flex;
    gap: var(--space-2);
    background: var(--color-bg-secondary);
    padding: var(--space-2);
    border-radius: var(--radius-xl);
    margin-bottom: var(--space-6);
    overflow-x: auto;
}

.tab-item {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-3) var(--space-5);
    border-radius: var(--radius-lg);
    font-size: var(--text-sm);
    font-weight: 500;
    color: var(--color-text-secondary);
    text-decoration: none;
    white-space: nowrap;
    transition: all 0.2s;
}

.tab-item:hover {
    color: var(--color-text-primary);
    background: var(--color-bg-tertiary);
}

.tab-item.active {
    color: var(--color-primary);
    background: rgba(var(--color-primary-rgb), 0.1);
}

.tab-icon {
    font-size: 1.25rem;
}

.profile-content {
    background: var(--color-bg-secondary);
    border-radius: var(--radius-xl);
    padding: var(--space-8);
    min-height: 400px;
    margin-bottom: var(--space-8);
}

.services-bar {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: var(--space-4);
    padding: var(--space-4);
    background: var(--color-bg-secondary);
    border-radius: var(--radius-xl);
}

.service-item {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    padding: var(--space-3);
}

.service-icon {
    font-size: 1.5rem;
}

.service-info {
    display: flex;
    flex-direction: column;
}

.service-label {
    font-weight: 600;
    font-size: var(--text-sm);
    color: var(--color-text-primary);
}

.service-desc {
    font-size: var(--text-xs);
    color: var(--color-primary);
    cursor: pointer;
}

@media (max-width: 768px) {
    .tab-nav {
        gap: var(--space-1);
    }

    .tab-item {
        padding: var(--space-2) var(--space-3);
    }

    .tab-label {
        display: none;
    }

    .services-bar {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>
