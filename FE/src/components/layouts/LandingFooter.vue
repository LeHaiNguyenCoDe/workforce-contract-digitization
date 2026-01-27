<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' })
}
</script>

<template>
    <footer class="bg-[#2C2C54] text-white mt-auto">
        <div class="container py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <!-- Brand & Contact -->
                <div>
                    <div class="flex items-center gap-2 mb-6">
                        <img src="../../assets/layout/logo.svg" alt="House Logo" class="w-20 h-auto" />
                    </div>
                    
                    <div class="space-y-3 text-sm text-slate-300">
                        <div class="flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                            <span>0947 225 188</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect width="20" height="16" x="2" y="4" rx="2"/>
                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                            </svg>
                            <span>House1996H@gmail.com</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            <span>Số 10, đường Cầu giấy<br/>Hà Nội</span>
                        </div>
                    </div>

                    <div class="mt-6">
                        <p class="text-sm text-slate-300 mb-3">{{ t('nav.footer.followMe') || 'Follow me !' }}</p>
                        <div class="flex gap-3">
                            <a href="#" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            </a>
                            <a href="#" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                            </a>
                            <a href="#" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Chi nhánh -->
                <div>
                    <h4 class="font-semibold text-white mb-4">{{ t('nav.footer.branches') || 'Chi nhánh' }}</h4>
                    <ul class="space-y-2 text-sm text-slate-300">
                        <li><a href="#" class="hover:text-white transition-colors">{{ t('nav.footer.hanoi') || 'Hà Nội' }}</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">{{ t('nav.footer.hochiminh') || 'Tp.Hồ Chí Minh' }}</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">{{ t('nav.footer.haiphong') || 'Hải Phòng' }}</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">{{ t('nav.footer.thanhhoa') || 'Thanh Hóa' }}</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">{{ t('nav.footer.danang') || 'Đà Nẵng' }}</a></li>
                    </ul>
                </div>

                <!-- Chính sách -->
                <div>
                    <h4 class="font-semibold text-white mb-4">{{ t('nav.footer.policies') || 'Chính sách' }}</h4>
                    <ul class="space-y-2 text-sm text-slate-300">
                        <li><RouterLink to="/policies/return" class="hover:text-white transition-colors">{{ t('nav.footer.return') || 'Đổi trả' }}</RouterLink></li>
                        <li><RouterLink to="/policies/payment" class="hover:text-white transition-colors">{{ t('nav.footer.payment') || 'Thanh toán' }}</RouterLink></li>
                        <li><RouterLink to="/policies/complaint" class="hover:text-white transition-colors">{{ t('nav.footer.complaint') || 'Khiếu nại' }}</RouterLink></li>
                        <li><RouterLink to="/policies/installment" class="hover:text-white transition-colors">{{ t('nav.footer.installment') || 'Trả góp' }}</RouterLink></li>
                    </ul>
                </div>

                <!-- Hỏi đáp -->
                <div>
                    <h4 class="font-semibold text-white mb-4">{{ t('nav.footer.faq') || 'Hỏi đáp' }}</h4>
                    <ul class="space-y-2 text-sm text-slate-300">
                        <li><RouterLink to="/products" class="hover:text-white transition-colors">{{ t('nav.footer.products') || 'Sản phẩm' }}</RouterLink></li>
                        <li><RouterLink to="/orders" class="hover:text-white transition-colors">{{ t('nav.footer.ordering') || 'Đặt hàng' }}</RouterLink></li>
                        <li><RouterLink to="/quality" class="hover:text-white transition-colors">{{ t('nav.footer.quality') || 'Chất lượng' }}</RouterLink></li>
                        <li><RouterLink to="/policies" class="hover:text-white transition-colors">{{ t('nav.footer.policiesLink') || 'Chính sách' }}</RouterLink></li>
                    </ul>
                </div>
            </div>

            <!-- Bottom -->
            <div class="pt-6 border-t border-white/10 flex justify-between items-center">
                <p class="text-sm text-slate-400">{{ t('nav.footer.copyright') || 'Copy right & Design by Lê Văn Trung' }}</p>
                <div class="flex gap-6 text-sm text-slate-400">
                    <RouterLink to="/complaint" class="hover:text-white transition-colors">{{ t('nav.footer.complaintLink') || 'Khiếu nại' }}</RouterLink>
                    <RouterLink to="/login" class="hover:text-white transition-colors">{{ t('nav.login') || 'Đăng nhập' }}</RouterLink>
                    <RouterLink to="/contact" class="hover:text-white transition-colors">{{ t('nav.footer.contact') || 'Tư vấn' }}</RouterLink>
                </div>
                <button @click="scrollToTop" class="w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="m18 15-6-6-6 6"/>
                    </svg>
                </button>
            </div>
        </div>
    </footer>
</template>
