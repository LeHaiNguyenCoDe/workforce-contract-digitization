<script setup lang="ts">
/**
 * Profile Info View
 * Uses useProfile composable for personal info and password updates
 */
import { ref, onMounted, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useProfile } from '../composables/useProfile'

const { t } = useI18n()

const {
    user,
    isSaving: isUpdating,
    message,
    updateProfile,
    changePassword
} = useProfile()

// Profile form
const profileForm = ref({
    name: '',
    phone: '',
    email: '',
    gender: '',
    birthday: ''
})

// Password form
const passwordForm = ref({
    current_password: '',
    new_password: '',
    confirm_password: ''
})

const passwordMessage = ref('')

const syncForm = () => {
    if (user.value) {
        profileForm.value.name = user.value.name || ''
        profileForm.value.email = user.value.email || ''
        profileForm.value.phone = (user.value as any).phone || ''
        profileForm.value.gender = (user.value as any).gender || ''
        profileForm.value.birthday = (user.value as any).birthday || ''
    }
}

onMounted(syncForm)
watch(user, syncForm)

const handleUpdateProfile = async () => {
    try {
        await updateProfile(profileForm.value)
    } catch (err) {
        // Error handled in composable/message
    }
}

const handleUpdatePassword = async () => {
    if (passwordForm.value.new_password !== passwordForm.value.confirm_password) {
        passwordMessage.value = t('common.confirmPasswordMatch')
        return
    }

    try {
        await changePassword(passwordForm.value)
        passwordForm.value = { current_password: '', new_password: '', confirm_password: '' }
        passwordMessage.value = t('auth.passwordChangeSuccess')
    } catch (error: any) {
        passwordMessage.value = error.response?.data?.message || t('auth.passwordChangeFailed')
    }
}
</script>

<template>
    <div class="space-y-12">
        <section>
            <h2 class="text-xl font-bold text-white mb-8 border-b border-white/5 pb-4">{{ t('common.personalInfo') }}</h2>

            <!-- Avatar Section -->
            <div class="flex flex-col items-center gap-4 mb-8">
                <div class="w-24 h-24 rounded-full bg-gradient-primary flex items-center justify-center text-3xl font-bold text-white shadow-xl shadow-primary/20">
                    <span>{{ user?.name?.charAt(0)?.toUpperCase() || 'U' }}</span>
                </div>
                <button class="text-sm text-primary hover:text-primary-light font-medium transition-colors">{{ t('common.changeAvatar') }}</button>
            </div>

            <!-- Profile Form -->
            <form @submit.prevent="handleUpdateProfile" class="max-w-2xl mx-auto space-y-6">
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="form-group md:col-span-2">
                        <label class="form-label">{{ t('common.phoneNumber') }}</label>
                        <input v-model="profileForm.phone" type="tel" class="form-input" placeholder="0901234567" />
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ t('common.fullName') }}</label>
                        <input v-model="profileForm.name" type="text" class="form-input" placeholder="Nguyễn Văn A" />
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input v-model="profileForm.email" type="email" class="form-input" placeholder="email@example.com" disabled />
                        <p class="text-[10px] text-slate-500 mt-1 italic">{{ t('common.emailCannotChange') }}</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ t('common.gender') }}</label>
                        <div class="flex gap-6 mt-2">
                            <label class="flex items-center gap-2 cursor-pointer group text-slate-300">
                                <input type="radio" v-model="profileForm.gender" value="male" class="text-primary focus:ring-primary bg-dark-700" />
                                <span class="group-hover:text-white transition-colors">{{ t('common.male') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer group text-slate-300">
                                <input type="radio" v-model="profileForm.gender" value="female" class="text-primary focus:ring-primary bg-dark-700" />
                                <span class="group-hover:text-white transition-colors">{{ t('common.female') }}</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ t('common.birthday') }}</label>
                        <input v-model="profileForm.birthday" type="date" class="form-input" />
                    </div>
                </div>

                <div v-if="message" class="p-4 rounded-xl text-center text-sm" 
                    :class="message.includes('thành công') ? 'bg-success/10 text-success' : 'bg-error/10 text-error'">
                    {{ message }}
                </div>

                <div class="flex justify-center pt-4">
                    <button type="submit" class="btn btn-primary px-12 py-3 font-bold" :disabled="isUpdating">
                        <span v-if="isUpdating" class="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin mr-2"></span>
                        {{ isUpdating ? t('common.updating') : t('common.saveInfo') }}
                    </button>
                </div>
            </form>
        </section>

        <!-- Password Section -->
        <section class="pt-12 border-t border-white/5">
            <h3 class="text-lg font-bold text-white mb-8 text-center">🔐 {{ t('common.changePassword') }}</h3>

            <form @submit.prevent="handleUpdatePassword" class="max-w-xl mx-auto space-y-6">
                <div class="form-group">
                    <label class="form-label">{{ t('common.currentPassword') }}</label>
                    <input v-model="passwordForm.current_password" type="password" class="form-input" />
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label class="form-label">{{ t('common.newPassword') }}</label>
                        <input v-model="passwordForm.new_password" type="password" class="form-input" />
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ t('auth.confirmPassword') }}</label>
                        <input v-model="passwordForm.confirm_password" type="password" class="form-input" />
                    </div>
                </div>

                <div v-if="passwordMessage" class="p-4 rounded-xl text-center text-sm"
                    :class="passwordMessage.includes('thành công') ? 'bg-success/10 text-success' : 'bg-error/10 text-error'">
                    {{ passwordMessage }}
                </div>

                <div class="flex justify-center">
                    <button type="submit" class="btn btn-secondary border border-white/10 px-8 py-3 font-bold" :disabled="isUpdating">
                        {{ t('common.changePassword') }}
                    </button>
                </div>
            </form>
        </section>
    </div>
</template>
