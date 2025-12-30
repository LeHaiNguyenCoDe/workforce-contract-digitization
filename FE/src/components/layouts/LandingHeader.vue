<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores'
import LanguageSwitcher from '@/shared/components/LanguageSwitcher.vue'

const { t } = useI18n()
const router = useRouter()
const authStore = useAuthStore()
const isMobileMenuOpen = ref(false)
const isUserMenuOpen = ref(false)

const handleLogout = async () => {
    isUserMenuOpen.value = false
    await authStore.logout()
    router.push('/')
}

const closeUserMenu = () => {
    isUserMenuOpen.value = false
}
</script>

<template>
    <header class="sticky top-0 z-50 glass">
        <div class="container py-4">
            <div class="flex items-center justify-between gap-8">
                <!-- Logo -->
                <RouterLink to="/" class="text-2xl font-bold font-display gradient-text">
                    Store
                </RouterLink>

                <!-- Navigation -->
                <nav class="hidden md:flex items-center gap-6">
                    <RouterLink to="/"
                        class="px-3 py-2 text-slate-300 font-medium rounded-lg hover:text-white hover:bg-primary/10 transition-all">
                        {{ t('nav.home') }}</RouterLink>
                    <RouterLink to="/products"
                        class="px-3 py-2 text-slate-300 font-medium rounded-lg hover:text-white hover:bg-primary/10 transition-all">
                        {{ t('nav.products') }}</RouterLink>
                    <RouterLink to="/articles"
                        class="px-3 py-2 text-slate-300 font-medium rounded-lg hover:text-white hover:bg-primary/10 transition-all">
                        {{ t('nav.articles') }}</RouterLink>
                    <RouterLink to="/promotions"
                        class="px-3 py-2 text-slate-300 font-medium rounded-lg hover:text-white hover:bg-primary/10 transition-all">
                        {{ t('nav.promotions') }}</RouterLink>
                </nav>

                <!-- Actions -->
                <div class="flex items-center gap-3">
                    <LanguageSwitcher />

                    <!-- Cart -->
                    <RouterLink to="/cart"
                        class="relative p-2 text-slate-400 hover:text-white hover:bg-primary/10 rounded-full transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <circle cx="8" cy="21" r="1" />
                            <circle cx="19" cy="21" r="1" />
                            <path
                                d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12" />
                        </svg>
                        <span v-if="authStore.cartCount > 0"
                            class="absolute -top-1 -right-1 w-5 h-5 text-xs font-semibold text-white bg-secondary rounded-full flex items-center justify-center">
                            {{ authStore.cartCount }}
                        </span>
                    </RouterLink>

                    <!-- User Dropdown -->
                    <template v-if="authStore.isAuthenticated">
                        <div class="relative" v-click-outside="closeUserMenu">
                            <!-- Avatar Button -->
                            <button @click="isUserMenuOpen = !isUserMenuOpen"
                                class="w-9 h-9 text-sm font-semibold text-white bg-gradient-primary rounded-full flex items-center justify-center hover:ring-2 hover:ring-primary/50 transition-all">
                                {{ authStore.userName.charAt(0) || 'U' }}
                            </button>

                            <!-- Dropdown Menu -->
                            <Transition enter-active-class="transition ease-out duration-200"
                                enter-from-class="opacity-0 translate-y-1" enter-to-class="opacity-100 translate-y-0"
                                leave-active-class="transition ease-in duration-150"
                                leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-1">
                                <div v-if="isUserMenuOpen"
                                    class="absolute right-0 top-full mt-2 w-56 rounded-xl bg-slate-800/95 backdrop-blur-lg border border-white/10 shadow-xl py-2 z-50">

                                    <!-- User Info -->
                                    <div class="px-4 py-3 border-b border-white/10">
                                        <p class="text-sm font-semibold text-white">{{ authStore.userName }}</p>
                                        <p class="text-xs text-slate-400">{{ authStore.user?.email }}</p>
                                    </div>

                                    <!-- Menu Items -->
                                    <div class="py-1">
                                        <RouterLink to="/profile" @click="closeUserMenu"
                                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-300 hover:text-white hover:bg-white/5 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="8" r="5" />
                                                <path d="M20 21a8 8 0 1 0-16 0" />
                                            </svg>
                                            {{ t('nav.profile') || 'Tài khoản của tôi' }}
                                        </RouterLink>

                                        <RouterLink to="/orders" @click="closeUserMenu"
                                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-300 hover:text-white hover:bg-white/5 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path
                                                    d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" />
                                                <path d="m3.3 7 8.7 5 8.7-5" />
                                                <path d="M12 22V12" />
                                            </svg>
                                            {{ t('nav.orders') || 'Đơn hàng của tôi' }}
                                        </RouterLink>

                                        <RouterLink to="/wishlist" @click="closeUserMenu"
                                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-300 hover:text-white hover:bg-white/5 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path
                                                    d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.29 1.51 4.04 3 5.5l7 7Z" />
                                            </svg>
                                            {{ t('nav.wishlist') || 'Sản phẩm yêu thích' }}
                                        </RouterLink>

                                        <!-- Admin Link -->
                                        <RouterLink v-if="authStore.isAdmin" to="/admin" @click="closeUserMenu"
                                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-primary hover:text-primary-light hover:bg-white/5 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path
                                                    d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.08a2 2 0 0 1 1 1.72v.5a2 2 0 0 1-1 1.72l-.15.08a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.38a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.72v-.5a2 2 0 0 1 1-1.72l.15-.08a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" />
                                                <circle cx="12" cy="12" r="3" />
                                            </svg>
                                            {{ t('nav.admin') }}
                                        </RouterLink>
                                    </div>

                                    <!-- Logout -->
                                    <div class="border-t border-white/10 pt-1 mt-1">
                                        <button @click="handleLogout"
                                            class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-400 hover:text-red-300 hover:bg-white/5 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                                <polyline points="16,17 21,12 16,7" />
                                                <line x1="21" x2="9" y1="12" y2="12" />
                                            </svg>
                                            {{ t('nav.logout') || 'Đăng xuất' }}
                                        </button>
                                    </div>
                                </div>
                            </Transition>
                        </div>
                    </template>
                    <template v-else>
                        <RouterLink to="/login" class="btn btn-primary btn-sm">
                            {{ t('nav.login') }}
                        </RouterLink>
                    </template>

                    <!-- Mobile Menu Button -->
                    <button @click="isMobileMenuOpen = !isMobileMenuOpen"
                        class="md:hidden p-2 text-slate-400 hover:text-white">
                        <svg v-if="!isMobileMenuOpen" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="4" x2="20" y1="12" y2="12" />
                            <line x1="4" x2="20" y1="6" y2="6" />
                            <line x1="4" x2="20" y1="18" y2="18" />
                        </svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 6 6 18" />
                            <path d="M6 6 18 18" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Navigation -->
            <nav v-if="isMobileMenuOpen" class="md:hidden mt-4 pt-4 border-t border-white/10 flex flex-col gap-2">
                <RouterLink to="/" class="px-3 py-2 text-slate-300 hover:text-white hover:bg-primary/10 rounded-lg">{{
                    t('nav.home')
                    }}</RouterLink>
                <RouterLink to="/products"
                    class="px-3 py-2 text-slate-300 hover:text-white hover:bg-primary/10 rounded-lg">{{
                        t('nav.products') }}</RouterLink>
                <RouterLink to="/articles"
                    class="px-3 py-2 text-slate-300 hover:text-white hover:bg-primary/10 rounded-lg">{{
                        t('nav.articles') }}</RouterLink>
                <RouterLink to="/promotions"
                    class="px-3 py-2 text-slate-300 hover:text-white hover:bg-primary/10 rounded-lg">{{
                        t('nav.promotions') }}</RouterLink>

                <!-- Mobile User Section -->
                <template v-if="authStore.isAuthenticated">
                    <div class="border-t border-white/10 mt-2 pt-2">
                        <RouterLink to="/profile"
                            class="px-3 py-2 text-slate-300 hover:text-white hover:bg-primary/10 rounded-lg flex items-center gap-2">
                            <span
                                class="w-6 h-6 text-xs font-semibold text-white bg-gradient-primary rounded-full flex items-center justify-center">
                                {{ authStore.userName.charAt(0) || 'U' }}
                            </span>
                            {{ authStore.userName }}
                        </RouterLink>
                        <RouterLink to="/orders"
                            class="px-3 py-2 text-slate-300 hover:text-white hover:bg-primary/10 rounded-lg">
                            {{ t('nav.orders') || 'Đơn hàng' }}
                        </RouterLink>
                        <button @click="handleLogout"
                            class="w-full text-left px-3 py-2 text-red-400 hover:text-red-300 hover:bg-primary/10 rounded-lg">
                            {{ t('nav.logout') || 'Đăng xuất' }}
                        </button>
                    </div>
                </template>
            </nav>
        </div>
    </header>
</template>
