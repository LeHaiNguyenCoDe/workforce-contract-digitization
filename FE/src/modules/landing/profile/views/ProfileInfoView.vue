<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores'
import httpClient from '@/plugins/api/httpClient'

const authStore = useAuthStore()

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

const isUpdatingProfile = ref(false)
const isUpdatingPassword = ref(false)
const profileMessage = ref('')
const passwordMessage = ref('')

onMounted(() => {
    if (authStore.user) {
        profileForm.value.name = authStore.user.name || ''
        profileForm.value.email = authStore.user.email || ''
        profileForm.value.phone = (authStore.user as any).phone || ''
        profileForm.value.gender = (authStore.user as any).gender || ''
        profileForm.value.birthday = (authStore.user as any).birthday || ''
    }
})

const updateProfile = async () => {
    isUpdatingProfile.value = true
    profileMessage.value = ''
    try {
        await httpClient.put('/frontend/profile', profileForm.value)
        profileMessage.value = 'Cập nhật thông tin thành công!'
        await authStore.fetchUser()
    } catch (error: any) {
        profileMessage.value = error.response?.data?.message || 'Cập nhật thất bại!'
    } finally {
        isUpdatingProfile.value = false
    }
}

const updatePassword = async () => {
    if (passwordForm.value.new_password !== passwordForm.value.confirm_password) {
        passwordMessage.value = 'Mật khẩu xác nhận không khớp!'
        return
    }

    isUpdatingPassword.value = true
    passwordMessage.value = ''
    try {
        await httpClient.put('/frontend/profile/password', {
            current_password: passwordForm.value.current_password,
            password: passwordForm.value.new_password,
            password_confirmation: passwordForm.value.confirm_password
        })
        passwordMessage.value = 'Đổi mật khẩu thành công!'
        passwordForm.value = { current_password: '', new_password: '', confirm_password: '' }
    } catch (error: any) {
        passwordMessage.value = error.response?.data?.message || 'Đổi mật khẩu thất bại!'
    } finally {
        isUpdatingPassword.value = false
    }
}
</script>

<template>
    <div class="profile-info">
        <h2 class="section-title">Thông tin tài khoản</h2>

        <!-- Avatar Section -->
        <div class="avatar-section">
            <div class="avatar">
                <span>{{ authStore.userName.charAt(0) || 'U' }}</span>
            </div>
            <button class="avatar-btn">Thay ảnh đại diện</button>
        </div>

        <!-- Profile Form -->
        <form @submit.prevent="updateProfile" class="profile-form">
            <div class="form-row">
                <div class="form-group">
                    <label>SĐT</label>
                    <input v-model="profileForm.phone" type="tel" class="form-input" placeholder="0901234567" />
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Họ và Tên</label>
                    <input v-model="profileForm.name" type="text" class="form-input" placeholder="Nguyễn Văn A" />
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input v-model="profileForm.email" type="email" class="form-input"
                        placeholder="email@example.com" />
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Giới tính</label>
                    <div class="radio-group">
                        <label class="radio-item">
                            <input type="radio" v-model="profileForm.gender" value="male" />
                            <span>Nam</span>
                        </label>
                        <label class="radio-item">
                            <input type="radio" v-model="profileForm.gender" value="female" />
                            <span>Nữ</span>
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label>Ngày sinh</label>
                    <input v-model="profileForm.birthday" type="date" class="form-input" />
                </div>
            </div>

            <div v-if="profileMessage" class="message" :class="{ success: profileMessage.includes('thành công') }">
                {{ profileMessage }}
            </div>

            <button type="submit" class="btn btn-primary" :disabled="isUpdatingProfile">
                {{ isUpdatingProfile ? 'Đang cập nhật...' : 'Cập nhật' }}
            </button>
        </form>

        <!-- Password Section -->
        <div class="password-section">
            <h3>Đổi mật khẩu</h3>

            <form @submit.prevent="updatePassword" class="password-form">
                <div class="form-group">
                    <label>Mật khẩu hiện tại</label>
                    <input v-model="passwordForm.current_password" type="password" class="form-input" />
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Mật khẩu mới</label>
                        <input v-model="passwordForm.new_password" type="password" class="form-input" />
                    </div>
                    <div class="form-group">
                        <label>Nhập lại mật khẩu mới</label>
                        <input v-model="passwordForm.confirm_password" type="password" class="form-input" />
                    </div>
                </div>

                <div v-if="passwordMessage" class="message"
                    :class="{ success: passwordMessage.includes('thành công') }">
                    {{ passwordMessage }}
                </div>

                <button type="submit" class="btn btn-secondary" :disabled="isUpdatingPassword">
                    {{ isUpdatingPassword ? 'Đang cập nhật...' : 'Cập nhật' }}
                </button>
            </form>
        </div>
    </div>
</template>

<style scoped>
.section-title {
    font-size: var(--text-xl);
    font-weight: 600;
    color: var(--color-primary);
    margin-bottom: var(--space-6);
    text-align: center;
}

.avatar-section {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--space-3);
    margin-bottom: var(--space-6);
}

.avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: 700;
    color: white;
}

.avatar-btn {
    font-size: var(--text-sm);
    color: var(--color-primary);
    background: none;
    border: none;
    cursor: pointer;
}

.profile-form,
.password-form {
    max-width: 600px;
    margin: 0 auto;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-4);
    margin-bottom: var(--space-4);
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}

.form-group label {
    font-size: var(--text-sm);
    font-weight: 500;
    color: var(--color-text-secondary);
}

.radio-group {
    display: flex;
    gap: var(--space-4);
}

.radio-item {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    cursor: pointer;
}

.password-section {
    margin-top: var(--space-8);
    padding-top: var(--space-6);
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.password-section h3 {
    font-size: var(--text-lg);
    margin-bottom: var(--space-4);
    text-align: center;
}

.message {
    padding: var(--space-3);
    border-radius: var(--radius-md);
    margin-bottom: var(--space-4);
    text-align: center;
    background: rgba(239, 68, 68, 0.1);
    color: var(--color-error);
}

.message.success {
    background: rgba(34, 197, 94, 0.1);
    color: var(--color-success);
}

.btn {
    display: block;
    margin: 0 auto;
    min-width: 150px;
}

@media (max-width: 640px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>
