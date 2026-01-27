<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { settingsService } from '@/plugins/api'
import type { GeneralSettings, PaymentSettings, ShippingSettings, SeoSettings } from '@/plugins/api'

const { t } = useI18n()

// State
const activeTab = ref<'general' | 'payment' | 'shipping' | 'seo'>('general')
const isLoading = ref(true)
const isSaving = ref(false)
const showSuccess = ref(false)

// Form data
const generalForm = ref<GeneralSettings>({
    store_name: '',
    store_email: '',
    store_phone: '',
    store_address: '',
    store_logo: '',
    currency: 'VND',
    timezone: 'Asia/Ho_Chi_Minh',
})

const paymentForm = ref<PaymentSettings>({
    cod_enabled: true,
    bank_transfer_enabled: true,
    vnpay_enabled: false,
    momo_enabled: false,
    bank_name: '',
    bank_account: '',
    bank_owner: '',
})

const shippingForm = ref<ShippingSettings>({
    free_shipping_threshold: 500000,
    default_shipping_fee: 30000,
    ghn_enabled: false,
    ghtk_enabled: false,
})

const seoForm = ref<SeoSettings>({
    meta_title: '',
    meta_description: '',
    meta_keywords: '',
    google_analytics: '',
    facebook_pixel: '',
})

// Fetch all settings
const fetchSettings = async () => {
    isLoading.value = true
    try {
        const data = await settingsService.getAll()
        if (data.general) generalForm.value = { ...generalForm.value, ...data.general }
        if (data.payment) paymentForm.value = { ...paymentForm.value, ...data.payment }
        if (data.shipping) shippingForm.value = { ...shippingForm.value, ...data.shipping }
        if (data.seo) seoForm.value = { ...seoForm.value, ...data.seo }
    } catch (error) {
        console.error('Failed to load settings:', error)
    } finally {
        isLoading.value = false
    }
}

// Save settings
const saveSettings = async () => {
    isSaving.value = true
    try {
        switch (activeTab.value) {
            case 'general':
                await settingsService.update('general', generalForm.value)
                break
            case 'payment':
                await settingsService.update('payment', paymentForm.value)
                break
            case 'shipping':
                await settingsService.update('shipping', shippingForm.value)
                break
            case 'seo':
                await settingsService.update('seo', seoForm.value)
                break
        }
        showSuccess.value = true
        setTimeout(() => showSuccess.value = false, 3000)
    } catch (error) {
        console.error('Failed to save settings:', error)
    } finally {
        isSaving.value = false
    }
}

// Handle logo upload
const handleLogoUpload = async (event: Event) => {
    const input = event.target as HTMLInputElement
    if (input.files?.length) {
        try {
            const url = await settingsService.uploadLogo(input.files[0])
            generalForm.value.store_logo = url
        } catch (error) {
            console.error('Failed to upload logo:', error)
        }
    }
}

const tabs = [
    { id: 'general', label: 'Thông tin chung', icon: 'settings' },
    { id: 'payment', label: 'Thanh toán', icon: 'revenue' },
    { id: 'shipping', label: 'Vận chuyển', icon: 'shipping' },
    { id: 'seo', label: 'SEO', icon: 'search' },
] as const

onMounted(fetchSettings)
</script>

<template>
    <div>
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-white">Cài đặt hệ thống</h1>
            <button @click="saveSettings" :disabled="isSaving"
                class="btn-primary flex items-center gap-2">
                <span v-if="isSaving" class="animate-spin">⏳</span>
                <BaseIcon v-else name="check" :size="18" />
                {{ isSaving ? 'Đang lưu...' : 'Lưu cấu hình' }}
            </button>
        </div>

        <!-- Success Message -->
        <div v-if="showSuccess" 
            class="mb-4 p-4 rounded-lg bg-success/20 border border-success/30 text-success flex items-center gap-2">
            <BaseIcon name="check" :size="20" />
            Đã lưu cấu hình thành công!
        </div>

        <!-- Tabs -->
        <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
            <button v-for="tab in tabs" :key="tab.id"
                @click="activeTab = tab.id"
                :class="[
                    'flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all whitespace-nowrap',
                    activeTab === tab.id
                        ? 'bg-primary text-white'
                        : 'bg-dark-700 text-slate-400 hover:bg-dark-600'
                ]">
                <BaseIcon :name="tab.icon" :size="18" />
                {{ tab.label }}
            </button>
        </div>

        <!-- Loading -->
        <div v-if="isLoading" class="card animate-pulse">
            <div v-for="i in 5" :key="i" class="mb-6">
                <div class="h-4 w-24 bg-dark-600 rounded mb-2"></div>
                <div class="h-10 bg-dark-600 rounded"></div>
            </div>
        </div>

        <!-- General Settings -->
        <div v-else-if="activeTab === 'general'" class="card">
            <h2 class="text-lg font-bold text-white mb-6">Thông tin cửa hàng</h2>
            
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm text-slate-400 mb-2">Tên cửa hàng</label>
                    <input v-model="generalForm.store_name" type="text" 
                        class="form-input" placeholder="Nhập tên cửa hàng">
                </div>
                
                <div>
                    <label class="block text-sm text-slate-400 mb-2">Email liên hệ</label>
                    <input v-model="generalForm.store_email" type="email" 
                        class="form-input" placeholder="email@example.com">
                </div>
                
                <div>
                    <label class="block text-sm text-slate-400 mb-2">Số điện thoại</label>
                    <input v-model="generalForm.store_phone" type="tel" 
                        class="form-input" placeholder="0123456789">
                </div>
                
                <div>
                    <label class="block text-sm text-slate-400 mb-2">Đơn vị tiền tệ</label>
                    <select v-model="generalForm.currency" class="form-input">
                        <option value="VND">VND - Việt Nam Đồng</option>
                        <option value="USD">USD - US Dollar</option>
                    </select>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm text-slate-400 mb-2">Địa chỉ</label>
                    <textarea v-model="generalForm.store_address" rows="2"
                        class="form-input" placeholder="Địa chỉ cửa hàng"></textarea>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm text-slate-400 mb-2">Logo</label>
                    <div class="flex items-center gap-4">
                        <div v-if="generalForm.store_logo" 
                            class="w-20 h-20 rounded-lg bg-dark-600 overflow-hidden">
                            <img :src="generalForm.store_logo" alt="Logo" class="w-full h-full object-contain">
                        </div>
                        <div v-else class="w-20 h-20 rounded-lg bg-dark-600 flex items-center justify-center">
                            <BaseIcon name="image" :size="32" class="text-slate-500" />
                        </div>
                        <label class="btn-secondary cursor-pointer">
                            <input type="file" accept="image/*" class="hidden" @change="handleLogoUpload">
                            Tải lên logo
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Settings -->
        <div v-else-if="activeTab === 'payment'" class="card">
            <h2 class="text-lg font-bold text-white mb-6">Cài đặt thanh toán</h2>
            
            <div class="space-y-6">
                <div class="flex items-center justify-between p-4 rounded-lg bg-dark-700">
                    <div>
                        <div class="font-medium text-white">Thanh toán khi nhận hàng (COD)</div>
                        <div class="text-sm text-slate-400">Cho phép khách hàng thanh toán khi nhận hàng</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" v-model="paymentForm.cod_enabled" class="sr-only peer">
                        <div class="w-11 h-6 bg-dark-600 peer-focus:ring-2 peer-focus:ring-primary/50 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                </div>
                
                <div class="flex items-center justify-between p-4 rounded-lg bg-dark-700">
                    <div>
                        <div class="font-medium text-white">Chuyển khoản ngân hàng</div>
                        <div class="text-sm text-slate-400">Cho phép thanh toán qua chuyển khoản</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" v-model="paymentForm.bank_transfer_enabled" class="sr-only peer">
                        <div class="w-11 h-6 bg-dark-600 peer-focus:ring-2 peer-focus:ring-primary/50 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                </div>
                
                <div v-if="paymentForm.bank_transfer_enabled" class="grid md:grid-cols-3 gap-4 p-4 rounded-lg bg-dark-700/50">
                    <div>
                        <label class="block text-sm text-slate-400 mb-2">Tên ngân hàng</label>
                        <input v-model="paymentForm.bank_name" type="text" class="form-input" placeholder="VD: Vietcombank">
                    </div>
                    <div>
                        <label class="block text-sm text-slate-400 mb-2">Số tài khoản</label>
                        <input v-model="paymentForm.bank_account" type="text" class="form-input" placeholder="Số tài khoản">
                    </div>
                    <div>
                        <label class="block text-sm text-slate-400 mb-2">Chủ tài khoản</label>
                        <input v-model="paymentForm.bank_owner" type="text" class="form-input" placeholder="Tên chủ TK">
                    </div>
                </div>

                <div class="flex items-center justify-between p-4 rounded-lg bg-dark-700">
                    <div>
                        <div class="font-medium text-white">VNPay</div>
                        <div class="text-sm text-slate-400">Thanh toán trực tuyến qua VNPay</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" v-model="paymentForm.vnpay_enabled" class="sr-only peer">
                        <div class="w-11 h-6 bg-dark-600 peer-focus:ring-2 peer-focus:ring-primary/50 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between p-4 rounded-lg bg-dark-700">
                    <div>
                        <div class="font-medium text-white">MoMo</div>
                        <div class="text-sm text-slate-400">Thanh toán qua ví MoMo</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" v-model="paymentForm.momo_enabled" class="sr-only peer">
                        <div class="w-11 h-6 bg-dark-600 peer-focus:ring-2 peer-focus:ring-primary/50 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Shipping Settings -->
        <div v-else-if="activeTab === 'shipping'" class="card">
            <h2 class="text-lg font-bold text-white mb-6">Cài đặt vận chuyển</h2>
            
            <div class="grid md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm text-slate-400 mb-2">Miễn phí vận chuyển từ (VND)</label>
                    <input v-model.number="shippingForm.free_shipping_threshold" type="number" 
                        class="form-input" placeholder="500000">
                    <p class="text-xs text-slate-500 mt-1">Đơn hàng từ giá trị này sẽ được miễn phí ship</p>
                </div>
                
                <div>
                    <label class="block text-sm text-slate-400 mb-2">Phí ship mặc định (VND)</label>
                    <input v-model.number="shippingForm.default_shipping_fee" type="number" 
                        class="form-input" placeholder="30000">
                </div>
            </div>

            <h3 class="font-medium text-white mb-4">Đối tác vận chuyển</h3>
            
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 rounded-lg bg-dark-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-orange-500/20 flex items-center justify-center text-orange-500 font-bold">
                            GHN
                        </div>
                        <div>
                            <div class="font-medium text-white">Giao Hàng Nhanh</div>
                            <div class="text-sm text-slate-400">Tích hợp API GHN</div>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" v-model="shippingForm.ghn_enabled" class="sr-only peer">
                        <div class="w-11 h-6 bg-dark-600 peer-focus:ring-2 peer-focus:ring-primary/50 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between p-4 rounded-lg bg-dark-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-green-500/20 flex items-center justify-center text-green-500 font-bold text-xs">
                            GHTK
                        </div>
                        <div>
                            <div class="font-medium text-white">Giao Hàng Tiết Kiệm</div>
                            <div class="text-sm text-slate-400">Tích hợp API GHTK</div>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" v-model="shippingForm.ghtk_enabled" class="sr-only peer">
                        <div class="w-11 h-6 bg-dark-600 peer-focus:ring-2 peer-focus:ring-primary/50 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                </div>
            </div>
        </div>

        <!-- SEO Settings -->
        <div v-else-if="activeTab === 'seo'" class="card">
            <h2 class="text-lg font-bold text-white mb-6">Cài đặt SEO</h2>
            
            <div class="space-y-6">
                <div>
                    <label class="block text-sm text-slate-400 mb-2">Meta Title</label>
                    <input v-model="seoForm.meta_title" type="text" 
                        class="form-input" placeholder="Tiêu đề trang web">
                </div>
                
                <div>
                    <label class="block text-sm text-slate-400 mb-2">Meta Description</label>
                    <textarea v-model="seoForm.meta_description" rows="3"
                        class="form-input" placeholder="Mô tả trang web (tối đa 160 ký tự)"></textarea>
                </div>
                
                <div>
                    <label class="block text-sm text-slate-400 mb-2">Meta Keywords</label>
                    <input v-model="seoForm.meta_keywords" type="text" 
                        class="form-input" placeholder="Từ khóa, cách nhau bằng dấu phẩy">
                </div>
                
                <hr class="border-white/10">
                
                <div>
                    <label class="block text-sm text-slate-400 mb-2">Google Analytics ID</label>
                    <input v-model="seoForm.google_analytics" type="text" 
                        class="form-input" placeholder="G-XXXXXXXXXX">
                </div>
                
                <div>
                    <label class="block text-sm text-slate-400 mb-2">Facebook Pixel ID</label>
                    <input v-model="seoForm.facebook_pixel" type="text" 
                        class="form-input" placeholder="XXXXXXXXXXXXXXX">
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.form-input {
    @apply w-full px-4 py-2.5 rounded-lg bg-dark-700 border border-white/10 text-white placeholder-slate-500 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all;
}

.btn-primary {
    @apply px-4 py-2 rounded-lg bg-primary text-white font-medium hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed transition-all;
}

.btn-secondary {
    @apply px-4 py-2 rounded-lg bg-dark-600 text-slate-300 font-medium hover:bg-slate-600 transition-all;
}
</style>
