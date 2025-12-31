<script setup lang="ts">
import { ref, computed } from 'vue'
import { RouterView, RouterLink, useRouter, useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores'
import LanguageSwitcher from '@/shared/components/LanguageSwitcher.vue'
import NotificationBell from '@/shared/components/admin/NotificationBell.vue'
import { useAutoConnect } from '@/modules/admin/chat/composables/useRealtime'

// Initialize real-time connection for notifications across all admin pages
useAutoConnect()

const { t } = useI18n()
const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const isSidebarCollapsed = ref(false)
const expandedMenus = ref<string[]>([]) // Start with no menu expanded, will be set based on route

interface MenuItem {
    icon: string
    path: string
    label: string
    exact?: boolean
    children?: MenuItem[]
    requiredPermission?: string  // Permission required to see this menu
}

// Check if user has a specific permission
const hasPermission = (permission: string): boolean => {
    if (!authStore.user) return false

    // Admin role always has all permissions
    if (authStore.user.roles?.some(r => r.name === 'admin')) return true

    // Check permissions array from API
    return authStore.user.permissions?.includes(permission) ?? false
}

// Check if user can see a menu item
const canSeeMenuItem = (item: MenuItem): boolean => {
    // No permission requirement = everyone can see
    if (!item.requiredPermission) return true

    // Check if user has the required permission
    return hasPermission(item.requiredPermission)
}

// Filter children by permission as well
const filterChildren = (item: MenuItem): MenuItem => {
    if (!item.children) return item
    return {
        ...item,
        children: item.children.filter(child => canSeeMenuItem(child))
    }
}

const menuItems: MenuItem[] = [
    { icon: 'dashboard', path: '/admin', label: 'admin.dashboard', exact: true, requiredPermission: 'view_dashboard' },

    // Bán hàng
    {
        icon: 'sales',
        path: '/admin/sales',
        label: 'admin.sales',
        requiredPermission: 'view_orders',
        children: [
            { icon: 'orders', path: '/admin/orders', label: 'order.title', requiredPermission: 'view_orders' },
            { icon: 'returns', path: '/admin/returns', label: 'admin.returns', requiredPermission: 'view_returns' },
            { icon: 'customers', path: '/admin/customers', label: 'admin.customers', requiredPermission: 'view_customers' },
        ]
    },

    // Kho
    {
        icon: 'warehouse',
        path: '/admin/warehouse',
        label: 'admin.warehouse',
        requiredPermission: 'view_warehouse',
        children: [
            { icon: 'products', path: '/admin/products', label: 'admin.products', requiredPermission: 'view_products' },
            { icon: 'categories', path: '/admin/categories', label: 'admin.categories', requiredPermission: 'view_categories' },
            { icon: 'batches', path: '/admin/warehouse/inbound-batches', label: 'admin.inboundBatches', requiredPermission: 'view_warehouse' },
            { icon: 'inbound', path: '/admin/warehouse/inbound-receipts', label: 'admin.inboundReceipts', requiredPermission: 'create_inbound' },
            { icon: 'outbound', path: '/admin/warehouse/outbound-receipts', label: 'admin.outboundReceipts', requiredPermission: 'create_outbound' },
            { icon: 'stocktake', path: '/admin/warehouse/adjustments', label: 'admin.adjustments', requiredPermission: 'adjust_stock' },
            { icon: 'inventory', path: '/admin/warehouse/inventory', label: 'admin.inventory', requiredPermission: 'view_warehouse' },
        ]
    },

    // Mua hàng
    {
        icon: 'purchase',
        path: '/admin/purchase',
        label: 'admin.purchase',
        requiredPermission: 'view_suppliers',
        children: [
            { icon: 'supplier', path: '/admin/warehouse/suppliers', label: 'admin.suppliers', requiredPermission: 'view_suppliers' },
        ]
    },

    // Tài chính
    {
        icon: 'finance',
        path: '/admin/finance',
        label: 'admin.finance',
        requiredPermission: 'view_finance',
        children: [
            { icon: 'dashboard', path: '/admin/finance/dashboard', label: 'common.overview', requiredPermission: 'view_finance' },
            { icon: 'expense', path: '/admin/finance/expenses', label: 'admin.expense', requiredPermission: 'create_transactions' },
            { icon: 'categories', path: '/admin/finance/expense-categories', label: 'admin.categories', requiredPermission: 'view_finance' },
            { icon: 'receivable', path: '/admin/finance/receivables', label: 'admin.receivables', requiredPermission: 'view_receivables' },
            { icon: 'payable', path: '/admin/finance/payables', label: 'admin.payables', requiredPermission: 'view_payables' },
        ]
    },

    // Báo cáo (Tạm thời ẩn)
    /*
    {
        icon: 'reports',
        path: '/admin/reports',
        label: 'Báo cáo',
        children: [
            { icon: 'sales-report', path: '/admin/reports/sales', label: 'Báo cáo bán hàng' },
            { icon: 'inventory-report', path: '/admin/reports/inventory', label: 'Báo cáo kho' },
            { icon: 'finance-report', path: '/admin/reports/pnl', label: 'Báo cáo P&L' },
        ]
    },
    */

    // Marketing
    {
        icon: 'marketing',
        path: '/admin/marketing',
        label: 'admin.marketing',
        requiredPermission: 'view_membership',
        children: [
            { icon: 'membership', path: '/admin/marketing/membership', label: 'admin.membership', requiredPermission: 'view_membership' },
            { icon: 'points', path: '/admin/marketing/points', label: 'admin.points', requiredPermission: 'view_points' },
            { icon: 'promotions', path: '/admin/promotions', label: 'admin.promotions', requiredPermission: 'view_promotions' },
            { icon: 'automation', path: '/admin/marketing/automations', label: 'admin.automation', requiredPermission: 'view_promotions' },
        ]
    },
    // Nội dung
    {
        icon: 'articles',
        path: '/admin/articles',
        label: 'admin.articles',
        requiredPermission: 'view_articles',
    },

    // Chat
    {
        icon: 'chat',
        path: '/admin/chat',
        label: 'common.chat.title',
    },

    // Cấu hình
    {
        icon: 'settings',
        path: '/admin/settings',
        label: 'admin.settings',
        requiredPermission: 'view_settings',
        children: [
            { icon: 'users', path: '/admin/users', label: 'admin.staff', requiredPermission: 'view_users' },
            { icon: 'permissions', path: '/admin/settings/permissions', label: 'admin.permissions', requiredPermission: 'view_permissions' },
            { icon: 'warehouses', path: '/admin/warehouse/list', label: 'admin.warehouses', requiredPermission: 'view_warehouses' },
            { icon: 'audit', path: '/admin/settings/audit-logs', label: 'admin.auditLogs', requiredPermission: 'view_audit_logs' },
        ]
    },
]

// Computed: filter menu items based on user role
const visibleMenuItems = computed(() => {
    return menuItems
        .filter(item => canSeeMenuItem(item))
        .map(item => filterChildren(item))
        .filter(item => !item.children || item.children.length > 0)  // Remove empty parent menus
})

const toggleSubmenu = (icon: string) => {
    const index = expandedMenus.value.indexOf(icon)
    if (index === -1) {
        // Close all other menus and open the clicked one (accordion behavior)
        expandedMenus.value = [icon]
    } else {
        // Close the clicked menu
        expandedMenus.value = []
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
                <template v-for="item in visibleMenuItems">
                    <!-- Menu item with children (submenu) -->
                    <div v-if="item.children && item.children.length > 0" :key="`parent-${item.path}`">
                        <button @click="toggleSubmenu(item.icon)"
                            class="w-full flex items-center gap-3 px-3 py-3 text-slate-400 rounded-xl hover:text-white hover:bg-primary/10 transition-all"
                            :class="{ 'bg-primary/10 text-white': isActiveRoute(item) }"
                            :title="isSidebarCollapsed ? t(item.label) : undefined">
                            <span class="flex-shrink-0">
                                <!-- Sales -->
                                <svg v-if="item.icon === 'sales'" xmlns="http://www.w3.org/2000/svg" width="20"
                                    height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="8" cy="21" r="1" />
                                    <circle cx="19" cy="21" r="1" />
                                    <path
                                        d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12" />
                                </svg>
                                <!-- Warehouse -->
                                <svg v-else-if="item.icon === 'warehouse'" xmlns="http://www.w3.org/2000/svg" width="20"
                                    height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                                    <polyline points="9 22 9 12 15 12 15 22" />
                                </svg>
                                <!-- Purchase -->
                                <svg v-else-if="item.icon === 'purchase'" xmlns="http://www.w3.org/2000/svg" width="20"
                                    height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" />
                                    <path d="M3 6h18" />
                                    <path d="M16 10a4 4 0 0 1-8 0" />
                                </svg>
                                <!-- Finance -->
                                <svg v-else-if="item.icon === 'finance'" xmlns="http://www.w3.org/2000/svg" width="20"
                                    height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" x2="12" y1="2" y2="22" />
                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                                </svg>
                                <!-- Reports -->
                                <svg v-else-if="item.icon === 'reports'" xmlns="http://www.w3.org/2000/svg" width="20"
                                    height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 3v18h18" />
                                    <path d="m19 9-5 5-4-4-3 3" />
                                </svg>
                                <!-- Marketing -->
                                <svg v-else-if="item.icon === 'marketing'" xmlns="http://www.w3.org/2000/svg" width="20"
                                    height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="m22 8-6 4 6 4V8Z" />
                                    <rect width="14" height="12" x="2" y="6" rx="2" ry="2" />
                                </svg>
                                <!-- Settings -->
                                <svg v-else-if="item.icon === 'settings'" xmlns="http://www.w3.org/2000/svg" width="20"
                                    height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path
                                        d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                                <!-- Default -->
                                <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10" />
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
                                <span>{{ t(child.label) }}</span>
                            </RouterLink>
                        </div>
                    </div>
                    <!-- Regular menu item (no children) -->
                    <RouterLink v-else :to="item.path" :key="`item-${item.path}`"
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
                            <!-- Chat -->
                            <svg v-else-if="item.icon === 'chat'" xmlns="http://www.w3.org/2000/svg" width="20"
                                height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
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
                    <NotificationBell />
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
                <RouterView />
            </main>
        </div>
    </div>
</template>
