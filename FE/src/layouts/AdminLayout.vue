<script setup lang="ts">
import { ref, computed } from 'vue'
import { RouterView, RouterLink, useRouter, useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores'
import LanguageSwitcher from '@/shared/components/LanguageSwitcher.vue'

const { t } = useI18n()
const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const isSidebarCollapsed = ref(false)
const expandedMenus = ref<string[]>(['warehouses']) // Default expanded

interface MenuItem {
    icon: string
    path: string
    label: string
    exact?: boolean
    children?: MenuItem[]
}

const menuItems: MenuItem[] = [
    { icon: 'dashboard', path: '/admin', label: 'admin.dashboard', exact: true },
    { icon: 'users', path: '/admin/users', label: 'admin.users' },
    { icon: 'products', path: '/admin/products', label: 'admin.products' },
    { icon: 'categories', path: '/admin/categories', label: 'admin.categories' },
    { icon: 'orders', path: '/admin/orders', label: 'admin.orders' },
    { icon: 'promotions', path: '/admin/promotions', label: 'admin.promotions' },
    {
        icon: 'warehouses',
        path: '/admin/warehouse',
        label: 'admin.warehouse',
        children: [
            { icon: 'box', path: '/admin/warehouse/products', label: 'Sản phẩm' },
            { icon: 'supplier', path: '/admin/warehouse/suppliers', label: 'Nhà cung cấp' },
            { icon: 'quality', path: '/admin/warehouse/quality', label: 'Kiểm tra CL' },
            { icon: 'list', path: '/admin/warehouse/list', label: 'Danh sách kho' },
        ]
    },
    { icon: 'articles', path: '/admin/articles', label: 'admin.articles' },
    { icon: 'reviews', path: '/admin/reviews', label: 'admin.reviews' },
]

const toggleSubmenu = (icon: string) => {
    const index = expandedMenus.value.indexOf(icon)
    if (index === -1) {
        expandedMenus.value.push(icon)
    } else {
        expandedMenus.value.splice(index, 1)
    }
}

const isMenuExpanded = (icon: string) => expandedMenus.value.includes(icon)

const isActiveRoute = (item: typeof menuItems[0]) => {
    if (item.exact) return route.path === item.path
    return route.path === item.path || route.path.startsWith(item.path + '/')
}

const handleLogout = async () => {
    await authStore.logout()
    router.push('/login')
}

const pageTitle = computed(() => {
    const currentItem = menuItems.find(item => isActiveRoute(item))
    return currentItem ? t(currentItem.label) : t('admin.dashboard')
})
</script>

<template>
    <div class="flex h-screen overflow-hidden bg-dark-900" :class="{ 'sidebar-collapsed': isSidebarCollapsed }">
        <!-- Sidebar -->
        <aside
            class="fixed left-0 top-0 bottom-0 z-50 flex flex-col bg-dark-800 border-r border-white/10 transition-all duration-300"
            :class="isSidebarCollapsed ? 'w-[72px]' : 'w-[260px]'">
            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b border-white/10">
                <RouterLink to="/admin" class="flex items-center gap-3">
                    <span class="text-2xl">⚡</span>
                    <span v-if="!isSidebarCollapsed" class="text-xl font-bold font-display text-white">Admin</span>
                </RouterLink>
                <button @click="isSidebarCollapsed = !isSidebarCollapsed"
                    class="p-2 text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="m15 18-6-6 6-6" v-if="!isSidebarCollapsed" />
                        <path d="m9 18 6-6-6-6" v-else />
                    </svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
                <template v-for="item in menuItems" :key="item.path">
                    <!-- Menu item with children (submenu) -->
                    <div v-if="item.children && item.children.length > 0">
                        <button @click="toggleSubmenu(item.icon)"
                            class="w-full flex items-center gap-3 px-3 py-3 text-slate-400 rounded-xl hover:text-white hover:bg-primary/10 transition-all"
                            :class="{ 'bg-primary/10 text-white': isActiveRoute(item) }"
                            :title="isSidebarCollapsed ? t(item.label) : undefined">
                            <span class="flex-shrink-0">
                                <!-- Warehouses -->
                                <svg v-if="item.icon === 'warehouses'" xmlns="http://www.w3.org/2000/svg" width="20"
                                    height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                                    <polyline points="9 22 9 12 15 12 15 22" />
                                </svg>
                            </span>
                            <span v-if="!isSidebarCollapsed" class="font-medium text-sm flex-1 text-left">{{
                                t(item.label) }}</span>
                            <svg v-if="!isSidebarCollapsed" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                class="transition-transform duration-200"
                                :class="{ 'rotate-180': isMenuExpanded(item.icon) }">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </button>
                        <!-- Submenu items -->
                        <div v-if="isMenuExpanded(item.icon) && !isSidebarCollapsed"
                            class="ml-4 mt-1 space-y-1 border-l-2 border-white/10 pl-3">
                            <RouterLink v-for="child in item.children" :key="child.path" :to="child.path"
                                class="flex items-center gap-2 px-3 py-2 text-slate-500 rounded-lg hover:text-white hover:bg-white/5 transition-all text-sm"
                                :class="{ 'text-primary bg-primary/10': route.path === child.path }">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                <span>{{ child.label }}</span>
                            </RouterLink>
                        </div>
                    </div>
                    <!-- Regular menu item (no children) -->
                    <RouterLink v-else :to="item.path"
                        class="flex items-center gap-3 px-3 py-3 text-slate-400 rounded-xl hover:text-white hover:bg-primary/10 transition-all"
                        :class="{ 'bg-gradient-primary text-white shadow-lg shadow-primary/25': isActiveRoute(item) }"
                        :title="isSidebarCollapsed ? t(item.label) : undefined">
                        <span class="flex-shrink-0">
                            <!-- Dashboard -->
                            <svg v-if="item.icon === 'dashboard'" xmlns="http://www.w3.org/2000/svg" width="20"
                                height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect width="7" height="9" x="3" y="3" rx="1" />
                                <rect width="7" height="5" x="14" y="3" rx="1" />
                                <rect width="7" height="9" x="14" y="12" rx="1" />
                                <rect width="7" height="5" x="3" y="16" rx="1" />
                            </svg>
                            <!-- Users -->
                            <svg v-else-if="item.icon === 'users'" xmlns="http://www.w3.org/2000/svg" width="20"
                                height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                            </svg>
                            <!-- Products -->
                            <svg v-else-if="item.icon === 'products'" xmlns="http://www.w3.org/2000/svg" width="20"
                                height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="m7.5 4.27 9 5.15" />
                                <path
                                    d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" />
                                <path d="m3.3 7 8.7 5 8.7-5" />
                                <path d="M12 22V12" />
                            </svg>
                            <!-- Categories -->
                            <svg v-else-if="item.icon === 'categories'" xmlns="http://www.w3.org/2000/svg" width="20"
                                height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z" />
                            </svg>
                            <!-- Orders -->
                            <svg v-else-if="item.icon === 'orders'" xmlns="http://www.w3.org/2000/svg" width="20"
                                height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect width="16" height="16" x="4" y="4" rx="2" />
                                <path d="M4 10h16" />
                                <path d="M12 4v6" />
                            </svg>
                            <!-- Promotions -->
                            <svg v-else-if="item.icon === 'promotions'" xmlns="http://www.w3.org/2000/svg" width="20"
                                height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="9" cy="9" r="2" />
                                <circle cx="15" cy="15" r="2" />
                                <path d="M7.5 16.5 16.5 7.5" />
                                <rect width="18" height="18" x="3" y="3" rx="2" />
                            </svg>
                            <!-- Articles -->
                            <svg v-else-if="item.icon === 'articles'" xmlns="http://www.w3.org/2000/svg" width="20"
                                height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 22h14a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v4" />
                                <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                                <path d="M2 15h10" />
                                <path d="M2 19h10" />
                            </svg>
                            <!-- Reviews -->
                            <svg v-else-if="item.icon === 'reviews'" xmlns="http://www.w3.org/2000/svg" width="20"
                                height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon
                                    points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                            </svg>
                            <!-- Default -->
                            <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10" />
                            </svg>
                        </span>
                        <span v-if="!isSidebarCollapsed" class="font-medium text-sm">{{ t(item.label) }}</span>
                    </RouterLink>
                </template>
            </nav>

            <!-- Footer -->
            <div class="p-4 border-t border-white/10">
                <RouterLink to="/"
                    class="flex items-center gap-3 px-3 py-3 text-slate-400 rounded-xl hover:text-white hover:bg-white/10 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                    <span v-if="!isSidebarCollapsed" class="font-medium text-sm">Về trang chủ</span>
                </RouterLink>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col h-screen transition-all duration-300"
            :class="isSidebarCollapsed ? 'ml-[72px]' : 'ml-[260px]'">
            <!-- Header (fixed) -->
            <header
                class="flex-shrink-0 flex items-center justify-between px-6 py-4 bg-dark-800/80 backdrop-blur-md border-b border-white/10">
                <h1 class="text-xl font-bold text-white">{{ pageTitle }}</h1>
                <div class="flex items-center gap-4">
                    <LanguageSwitcher />
                    <div class="flex items-center gap-3">
                        <div class="text-right">
                            <p class="text-sm font-medium text-white">{{ authStore.userName }}</p>
                            <p class="text-xs text-slate-500">Admin</p>
                        </div>
                        <button @click="handleLogout"
                            class="p-2 text-slate-400 hover:text-error hover:bg-error/10 rounded-lg transition-colors"
                            title="Đăng xuất">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                <polyline points="16 17 21 12 16 7" />
                                <line x1="21" x2="9" y1="12" y2="12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="flex-1 overflow-hidden">
                <RouterView v-slot="{ Component }">
                    <transition enter-active-class="transition-all duration-200 ease-out"
                        enter-from-class="opacity-0 translate-y-2" enter-to-class="opacity-100 translate-y-0"
                        leave-active-class="transition-all duration-150 ease-in" leave-from-class="opacity-100"
                        leave-to-class="opacity-0" mode="out-in">
                        <component :is="Component" />
                    </transition>
                </RouterView>
            </main>
        </div>
    </div>
</template>
