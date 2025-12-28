<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores'

const { t } = useI18n()
const router = useRouter()
const authStore = useAuthStore()

const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
})

const errors = ref<Record<string, string>>({})
const isSubmitting = computed(() => authStore.isLoading)

const validateForm = () => {
    errors.value = {}

    if (!form.value.name) errors.value.name = t('validation.required')
    if (!form.value.email) {
        errors.value.email = t('validation.required')
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) {
        errors.value.email = t('validation.email')
    }
    if (!form.value.password) {
        errors.value.password = t('validation.required')
    } else if (form.value.password.length < 8) {
        errors.value.password = t('validation.minLength', { min: 8 })
    }
    if (form.value.password !== form.value.password_confirmation) {
        errors.value.password_confirmation = t('validation.passwordMismatch')
    }

    return Object.keys(errors.value).length === 0
}

const handleSubmit = async () => {
    if (!validateForm()) return

    const success = await authStore.register(form.value.name, form.value.email, form.value.password)

    if (success) {
        router.push('/login')
    } else {
        errors.value.form = 'Đăng ký thất bại. Vui lòng thử lại.'
    }
}
</script>

<template>
    <div class="min-h-[70vh] flex items-center justify-center py-12">
        <div class="container max-w-md">
            <div class="card">
                <!-- Header -->
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-white mb-2">{{ t('auth.register') }}</h1>
                    <p class="text-slate-400">Tạo tài khoản mới để bắt đầu mua sắm</p>
                </div>

                <!-- Form -->
                <form @submit.prevent="handleSubmit" class="space-y-4">
                    <div v-if="errors.form" class="form-error">{{ errors.form }}</div>

                    <div class="form-group">
                        <label for="name" class="form-label">{{ t('auth.name') }}</label>
                        <input id="name" v-model="form.name" type="text" class="form-input"
                            :class="{ 'input-error': errors.name }" />
                        <span v-if="errors.name" class="error-text">{{ errors.name }}</span>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">{{ t('auth.email') }}</label>
                        <input id="email" v-model="form.email" type="email" class="form-input"
                            :class="{ 'input-error': errors.email }" placeholder="example@email.com" />
                        <span v-if="errors.email" class="error-text">{{ errors.email }}</span>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">{{ t('auth.password') }}</label>
                        <input id="password" v-model="form.password" type="password" class="form-input"
                            :class="{ 'input-error': errors.password }" />
                        <span v-if="errors.password" class="error-text">{{ errors.password }}</span>
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">{{ t('auth.confirmPassword') }}</label>
                        <input id="password_confirmation" v-model="form.password_confirmation" type="password"
                            class="form-input" :class="{ 'input-error': errors.password_confirmation }" />
                        <span v-if="errors.password_confirmation" class="error-text">{{ errors.password_confirmation
                            }}</span>
                    </div>

                    <button type="submit" class="btn btn-primary w-full mt-6" :disabled="isSubmitting">
                        {{ isSubmitting ? t('common.loading') : t('auth.register') }}
                    </button>
                </form>

                <div class="mt-8 pt-6 border-t border-white/10 text-center">
                    <p class="text-sm text-slate-400">
                        {{ t('auth.hasAccount') }}
                        <RouterLink to="/login" class="text-primary font-medium hover:text-primary-light">{{
                            t('auth.login') }}</RouterLink>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
