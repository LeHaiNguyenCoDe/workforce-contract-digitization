<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const currentYear = new Date().getFullYear()
</script>

<template>
    <footer class="bg-dark-800 border-t border-white/10 mt-auto">
        <div class="container py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <!-- Brand -->
                <div class="md:col-span-2">
                    <h3 class="text-2xl font-bold font-display gradient-text mb-4">Store</h3>
                    <p class="text-slate-500 text-sm max-w-md">
                        Nền tảng mua sắm trực tuyến hàng đầu với hàng ngàn sản phẩm chất lượng cao.
                    </p>
                </div>

                <!-- Links -->
                <div>
                    <h4 class="font-semibold text-white mb-4">{{ t('nav.products') }}</h4>
                    <ul class="space-y-2">
                        <li>
                            <RouterLink to="/products"
                                class="text-sm text-slate-500 hover:text-primary-light transition-colors">{{
                                t('nav.products') }}</RouterLink>
                        </li>
                        <li>
                            <RouterLink to="/promotions"
                                class="text-sm text-slate-500 hover:text-primary-light transition-colors">{{
                                t('nav.promotions') }}</RouterLink>
                        </li>
                        <li>
                            <RouterLink to="/articles"
                                class="text-sm text-slate-500 hover:text-primary-light transition-colors">{{
                                t('nav.articles') }}</RouterLink>
                        </li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-semibold text-white mb-4">{{ t('nav.profile') }}</h4>
                    <ul class="space-y-2">
                        <li>
                            <RouterLink to="/profile"
                                class="text-sm text-slate-500 hover:text-primary-light transition-colors">{{
                                t('nav.profile') }}</RouterLink>
                        </li>
                        <li>
                            <RouterLink to="/orders"
                                class="text-sm text-slate-500 hover:text-primary-light transition-colors">{{
                                t('nav.orders') }}</RouterLink>
                        </li>
                        <li>
                            <RouterLink to="/wishlist"
                                class="text-sm text-slate-500 hover:text-primary-light transition-colors">{{
                                t('nav.wishlist') }}</RouterLink>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom -->
            <div class="pt-8 border-t border-white/10 text-center">
                <p class="text-sm text-slate-500">&copy; {{ currentYear }} Store. All rights reserved.</p>
            </div>
        </div>
    </footer>
</template>
