<script setup lang="ts">
/**
 * Login View
 * Uses useAuth composable for authentication logic
 */
import { ref, computed } from 'vue'
import { useRouter, useRoute, RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useAuth } from '../composables/useAuth'
import { authConfig } from '../configs'

const { t } = useI18n()
const router = useRouter()
const route = useRoute()

// Use composable
const { isLoading, login } = useAuth()

// Form state
const form = ref({
    email: '',
    password: ''
})

const errors = ref<Record<string, string>>({})
const isSubmitting = computed(() => isLoading.value)

// Validation
const validateForm = () => {
    errors.value = {}
    if (!form.value.email) {
        errors.value.email = t('validation.required')
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) {
        errors.value.email = t('validation.email')
    }
    if (!form.value.password) {
        errors.value.password = t('validation.required')
    } else if (form.value.password.length < authConfig.minPasswordLength) {
        errors.value.password = authConfig.passwordRequirements
    }
    return Object.keys(errors.value).length === 0
}

// Handle submit
const handleSubmit = async () => {
    if (!validateForm()) return

    try {
        const success = await login(form.value)
        if (success) {
            const redirect = route.query.redirect as string
            router.push(redirect || '/')
        }
    } catch (error: any) {
        errors.value.form = error.message || t('validation.loginFailed')
    }
}
</script>

<template>
    <div class="min-h-[70vh] flex items-center justify-center py-12">
        <div class="container max-w-md">
            <div class="card">
                <!-- Header -->
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-white mb-2">{{ t('auth.login') }}</h1>
                    <p class="text-slate-400">Đăng nhập để tiếp tục mua sắm</p>
                </div>

                <!-- Form -->
                <form @submit.prevent="handleSubmit" class="space-y-4">
                    <!-- Form Error -->
                    <div v-if="errors.form" class="p-4 rounded-lg bg-error/10 border border-error text-error text-sm">
                        {{ errors.form }}
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email" class="form-label">{{ t('auth.email') }}</label>
                        <input id="email" v-model="form.email" type="email" class="form-input"
                            :class="{ 'border-error': errors.email }" placeholder="example@email.com" />
                        <span v-if="errors.email" class="text-error text-sm mt-1">{{ errors.email }}</span>
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label for="password" class="form-label">{{ t('auth.password') }}</label>
                        <input id="password" v-model="form.password" type="password" class="form-input"
                            :class="{ 'border-error': errors.password }" />
                        <span v-if="errors.password" class="text-error text-sm mt-1">{{ errors.password }}</span>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn btn-primary w-full mt-6" :disabled="isSubmitting">
                        <span v-if="isSubmitting" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            {{ t('common.loading') }}
                        </span>
                        <span v-else>{{ t('auth.login') }}</span>
                    </button>
                </form>

                <!-- Footer -->
                <div class="mt-8 pt-6 border-t border-white/10 text-center">
                    <p class="text-sm text-slate-400">
                        {{ t('auth.noAccount') }}
                        <RouterLink to="/register" class="text-primary font-medium hover:text-primary-light">
                            {{ t('auth.register') }}
                        </RouterLink>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
