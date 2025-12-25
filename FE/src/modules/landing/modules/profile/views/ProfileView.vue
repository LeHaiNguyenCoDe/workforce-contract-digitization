<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import apiClient from '@/plugins/api/httpClient'
import { useAuthStore } from '@/stores/auth'
import type { User, ApiResponse } from '@/api/types'

const router = useRouter()
const authStore = useAuthStore()

const user = ref<User | null>(null)
const isLoading = ref(true)
const isEditing = ref(false)
const isSaving = ref(false)

const form = ref({ name: '' })
const errors = ref<Record<string, string>>({})

const fetchProfile = async () => {
    isLoading.value = true
    try {
        const response = await apiClient.get<ApiResponse<User>>('/frontend/profile')
        user.value = response.data.data || null
        form.value.name = user.value?.name || ''
    } catch (error) {
        console.error('Failed to fetch profile:', error)
    } finally {
        isLoading.value = false
    }
}

const handleSave = async () => {
    if (!form.value.name.trim()) {
        errors.value.name = 'Tên không được để trống'
        return
    }

    isSaving.value = true
    try {
        const response = await apiClient.put<ApiResponse<User>>('/frontend/profile', { name: form.value.name })
        user.value = response.data.data || user.value
        isEditing.value = false
        errors.value = {}
    } catch (error) {
        console.error('Failed to update profile:', error)
        errors.value.form = 'Không thể cập nhật thông tin'
    } finally {
        isSaving.value = false
    }
}

const handleLogout = async () => {
    await authStore.logout()
    router.push('/login')
}

onMounted(fetchProfile)
</script>

<template>
    <div class="profile-page">
        <div class="container">
            <h1>Tài khoản của tôi</h1>

            <div v-if="isLoading" class="skeleton-block"></div>

            <div v-else-if="user" class="profile-content">
                <div class="profile-card card">
                    <div class="profile-header">
                        <div class="avatar">{{ user.name.charAt(0) }}</div>
                        <div class="user-info">
                            <h2>{{ user.name }}</h2>
                            <p>{{ user.email }}</p>
                        </div>
                    </div>

                    <div v-if="!isEditing" class="profile-details">
                        <div class="detail-row">
                            <span class="label">Họ và tên</span>
                            <span>{{ user.name }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Email</span>
                            <span>{{ user.email }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Ngôn ngữ</span>
                            <span>{{ user.language === 'vi' ? 'Tiếng Việt' : 'English' }}</span>
                        </div>

                        <div class="profile-actions">
                            <button class="btn btn-secondary" @click="isEditing = true">Chỉnh sửa</button>
                            <button class="btn btn-outline danger" @click="handleLogout">Đăng xuất</button>
                        </div>
                    </div>

                    <form v-else @submit.prevent="handleSave" class="edit-form">
                        <div v-if="errors.form" class="form-error">{{ errors.form }}</div>

                        <div class="form-group">
                            <label class="form-label">Họ và tên</label>
                            <input v-model="form.name" type="text" class="form-input"
                                :class="{ 'input-error': errors.name }" />
                            <span v-if="errors.name" class="error-text">{{ errors.name }}</span>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary" :disabled="isSaving">
                                {{ isSaving ? 'Đang lưu...' : 'Lưu thay đổi' }}
                            </button>
                            <button type="button" class="btn btn-secondary" @click="isEditing = false">Hủy</button>
                        </div>
                    </form>
                </div>

                <div class="quick-links">
                    <RouterLink to="/orders" class="quick-link card">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" />
                            <path d="M3 6h18" />
                            <path d="M16 10a4 4 0 0 1-8 0" />
                        </svg>
                        <span>Đơn hàng của tôi</span>
                    </RouterLink>
                    <RouterLink to="/wishlist" class="quick-link card">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path
                                d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" />
                        </svg>
                        <span>Danh sách yêu thích</span>
                    </RouterLink>
                    <RouterLink to="/loyalty" class="quick-link card">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <polygon
                                points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                        </svg>
                        <span>Điểm thưởng</span>
                    </RouterLink>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.profile-page h1 {
    margin-bottom: var(--space-8);
}

.skeleton-block {
    height: 300px;
    background: var(--color-bg-tertiary);
    border-radius: var(--radius-lg);
    animation: pulse 2s infinite;
}

.profile-content {
    display: grid;
    gap: var(--space-6);
}

.profile-header {
    display: flex;
    align-items: center;
    gap: var(--space-4);
    padding-bottom: var(--space-6);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    margin-bottom: var(--space-4);
}

.avatar {
    width: 64px;
    height: 64px;
    font-size: var(--text-2xl);
    font-weight: 700;
    color: white;
    background: var(--gradient-primary);
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
}

.user-info h2 {
    margin-bottom: var(--space-1);
}

.user-info p {
    color: var(--color-text-muted);
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: var(--space-3) 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.detail-row .label {
    color: var(--color-text-muted);
}

.profile-actions {
    display: flex;
    gap: var(--space-3);
    margin-top: var(--space-6);
}

.btn.danger {
    color: var(--color-error);
    border-color: var(--color-error);
}

.btn.danger:hover {
    background: var(--color-error);
    color: white;
}

.edit-form {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.form-error {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid var(--color-error);
    color: var(--color-error);
    padding: var(--space-3);
    border-radius: var(--radius-md);
}

.input-error {
    border-color: var(--color-error) !important;
}

.error-text {
    font-size: var(--text-xs);
    color: var(--color-error);
    margin-top: var(--space-1);
}

.form-actions {
    display: flex;
    gap: var(--space-3);
}

.quick-links {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--space-4);
}

.quick-link {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    padding: var(--space-4);
    color: var(--color-text-primary);
}

.quick-link svg {
    color: var(--color-primary);
}
</style>
