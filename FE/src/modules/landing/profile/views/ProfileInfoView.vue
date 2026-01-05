<script setup lang="ts">
/**
 * Profile Info View - Redesigned to match minimalist mockup
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
    gender: 'male',
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
        profileForm.value.gender = (user.value as any).gender || 'male'
        profileForm.value.birthday = (user.value as any).birthday || ''
    }
}

onMounted(syncForm)
watch(user, syncForm)

const handleUpdateProfile = async () => {
    try {
        await updateProfile(profileForm.value)
    } catch (err) {
        // Error handled in composable
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
    <div class="profile-info-container max-w-[800px] mx-auto pb-10">
        <!-- Title -->
        <h1 class="text-3xl font-medium text-[#9F7A5F] text-center mb-10">
            Thông tin của tôi
        </h1>

        <!-- Avatar Section -->
        <div class="flex flex-col items-center gap-6 mb-12">
            <div class="relative group">
                <div
                    class="w-32 h-32 rounded-full bg-[#FEFBF2] flex items-center justify-center text-4xl font-bold text-[#9F7A5F] border-2 border-[#D9D9D9] overflow-hidden shadow-sm">
                    <img v-if="(user as any)?.avatar" :src="(user as any).avatar" class="w-full h-full object-cover" />
                    <span v-else>{{ user?.name?.charAt(0)?.toUpperCase() || 'U' }}</span>
                </div>
                <div
                    class="absolute bottom-0 right-0 w-8 h-8 bg-white border border-[#D9D9D9] rounded-full flex items-center justify-center cursor-pointer shadow-sm hover:bg-gray-50">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9F7A5F" stroke-width="2">
                        <path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z" />
                    </svg>
                </div>
            </div>
            <button
                class="px-8 py-2 bg-[#9F7A5F] text-white rounded-lg font-medium hover:bg-[#8A6A52] transition-colors">
                Thay ảnh đại diện
            </button>
        </div>

        <!-- Personal Info Form -->
        <form @submit.prevent="handleUpdateProfile" class="space-y-8 mb-16">
            <div class="grid md:grid-cols-2 gap-8">
                <div class="space-y-2">
                    <label class="block text-xl font-bold text-black">Họ và tên</label>
                    <input v-model="profileForm.name" type="text"
                        class="w-full px-4 py-3 bg-white border border-[#D9D9D9] rounded-lg focus:outline-none focus:border-[#9F7A5F]"
                        placeholder="Lê Văn Trung" />
                </div>
                <div class="space-y-2">
                    <label class="block text-xl font-bold text-black">Email</label>
                    <input v-model="profileForm.email" type="email"
                        class="w-full px-4 py-3 bg-white border border-[#D9D9D9] rounded-lg focus:outline-none focus:border-[#9F7A5F] opacity-70 cursor-not-allowed"
                        disabled />
                </div>
                <div class="space-y-2">
                    <label class="block text-xl font-bold text-black">SĐT</label>
                    <input v-model="profileForm.phone" type="tel"
                        class="w-full px-4 py-3 bg-white border border-[#D9D9D9] rounded-lg focus:outline-none focus:border-[#9F7A5F]"
                        placeholder="0947225188" />
                </div>
                <div class="space-y-2">
                    <label class="block text-xl font-bold text-black">Ngày sinh</label>
                    <input v-model="profileForm.birthday" type="date"
                        class="w-full px-4 py-3 bg-white border border-[#D9D9D9] rounded-lg focus:outline-none focus:border-[#9F7A5F]" />
                </div>
            </div>

            <!-- Gender -->
            <div class="space-y-2">
                <label class="block text-xl font-bold text-black">Giới tính</label>
                <div class="flex gap-8 mt-4">
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="radio" v-model="profileForm.gender" value="male"
                            class="w-5 h-5 accent-[#9F7A5F]" />
                        <span class="text-lg font-medium">Nam</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="radio" v-model="profileForm.gender" value="female"
                            class="w-5 h-5 accent-[#9F7A5F]" />
                        <span class="text-lg font-medium">Nữ</span>
                    </label>
                </div>
            </div>

            <!-- Message -->
            <div v-if="message" class="p-4 rounded-lg text-center"
                :class="message.includes('thành công') ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600'">
                {{ message }}
            </div>

            <div class="flex justify-center pt-6">
                <button type="submit"
                    class="px-16 py-3 bg-[#9F7A5F] text-white rounded-lg text-2xl font-medium hover:bg-[#8A6A52] transition-colors shadow-sm disabled:opacity-70"
                    :disabled="isUpdating">
                    <span v-if="isUpdating"
                        class="w-6 h-6 border-2 border-white/30 border-t-white rounded-full animate-spin inline-block mr-2"></span>
                    {{ isUpdating ? 'Đang lưu...' : 'Lưu thông tin' }}
                </button>
            </div>
        </form>

        <!-- Divider -->
        <hr class="border-[#D9D9D9] mb-16" />

        <!-- Password Section -->
        <div class="space-y-10">
            <h2 class="text-3xl font-medium text-[#9F7A5F] text-center">Đổi mật khẩu</h2>

            <form @submit.prevent="handleUpdatePassword" class="space-y-8 max-w-[600px] mx-auto">
                <div class="space-y-2">
                    <label class="block text-xl font-bold text-black">Mật khẩu cũ</label>
                    <input v-model="passwordForm.current_password" type="password"
                        class="w-full px-4 py-3 bg-white border border-[#D9D9D9] rounded-lg focus:outline-none focus:border-[#9F7A5F]" />
                </div>
                <div class="space-y-2">
                    <label class="block text-xl font-bold text-black">Mật khẩu mới</label>
                    <input v-model="passwordForm.new_password" type="password"
                        class="w-full px-4 py-3 bg-white border border-[#D9D9D9] rounded-lg focus:outline-none focus:border-[#9F7A5F]" />
                </div>
                <div class="space-y-2">
                    <label class="block text-xl font-bold text-black">Xác nhận mật khẩu</label>
                    <input v-model="passwordForm.confirm_password" type="password"
                        class="w-full px-4 py-3 bg-white border border-[#D9D9D9] rounded-lg focus:outline-none focus:border-[#9F7A5F]" />
                </div>

                <div v-if="passwordMessage" class="p-4 rounded-lg text-center"
                    :class="passwordMessage.includes('thành công') ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600'">
                    {{ passwordMessage }}
                </div>

                <div class="flex justify-center">
                    <button type="submit"
                        class="px-16 py-3 bg-[#9F7A5F] text-white rounded-lg text-2xl font-medium hover:bg-[#8A6A52] transition-colors shadow-sm disabled:opacity-70"
                        :disabled="isUpdating">
                        Đổi mật khẩu
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<style scoped>
/* Optional: specific styling for the date input to match the theme better */
input[type="date"]::-webkit-calendar-picker-indicator {
    filter: invert(53%) sepia(16%) saturate(692%) hue-rotate(341deg) brightness(92%) contrast(87%);
}
</style>
