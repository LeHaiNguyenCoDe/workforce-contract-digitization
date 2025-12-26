<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useUserStore, type User } from '../store/store'
import { useSwal } from '@/shared/utils'
import BaseModal from '@/shared/components/BaseModal.vue'

const { t } = useI18n()
const store = useUserStore()
const swal = useSwal()

// Local state
const searchQuery = ref('')
const showModal = ref(false)
const editingUser = ref<User | null>(null)

// Computed
const filteredUsers = computed(() => {
  let result = store.users
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(
      u => u.name.toLowerCase().includes(query) || u.email.toLowerCase().includes(query)
    )
  }
  return result
})

// Methods
function formatDate(date: string) {
  return new Date(date).toLocaleDateString('vi-VN')
}

function setSearchQuery(value: string) {
  searchQuery.value = value
  if (store.currentPage !== 1) {
    store.setPage(1)
  }
}

function changePage(page: number) {
  store.setPage(page)
  store.fetchUsers({ search: searchQuery.value })
}

function openCreateModal() {
  editingUser.value = null
  store.resetUserForm()
  showModal.value = true
}

function openEditModal(user: User) {
  editingUser.value = user
  store.userForm.name = user.name
  store.userForm.email = user.email
  store.userForm.phone = user.phone || ''
  store.userForm.password = '' // Don't fill password for edit
  store.userForm.role = user.roles?.[0]?.name || 'customer'
  store.userForm.is_active = user.active !== false
  showModal.value = true
}

async function saveUser() {
  if (store.isSaving) return

  const form = store.userForm
  if (!form.name?.trim() || !form.email?.trim()) {
    await swal.warning('Vui lòng điền tên và email!')
    return
  }

  try {
    const payload: any = {
      name: form.name.trim(),
      email: form.email.trim(),
      role: form.role,
      is_active: form.is_active
    }
    if (form.phone?.trim()) payload.phone = form.phone.trim()
    if (form.password?.trim()) payload.password = form.password.trim()

    if (editingUser.value) {
      await store.updateUser(editingUser.value.id, payload)
      await swal.success('Cập nhật người dùng thành công!')
    } else {
      if (!form.password?.trim()) {
        await swal.warning('Vui lòng nhập mật khẩu cho người dùng mới!')
        return
      }
      await store.createUser(payload)
      await swal.success('Tạo người dùng thành công!')
    }

    showModal.value = false
    editingUser.value = null
    store.resetUserForm()
  } catch (error: any) {
    console.error('Failed to save user:', error)
    await swal.error(error.response?.data?.message || 'Lưu thất bại!')
  }
}

async function deleteUser(id: number) {
  const confirmed = await swal.confirmDelete('Bạn có chắc muốn xóa người dùng này?')
  if (!confirmed) return

  try {
    await store.deleteUser(id)
    await swal.success('Xóa người dùng thành công!')
  } catch (error: any) {
    console.error('Failed to delete user:', error)
    await swal.error(error.response?.data?.message || 'Xóa thất bại!')
  }
}

// Lifecycle
onMounted(async () => {
  await store.fetchUsers()
})
</script>

<template>
  <div class="h-full flex flex-col p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6 flex-shrink-0">
      <div>
        <h1 class="text-2xl font-bold text-white">{{ t('admin.users') }}</h1>
        <p class="text-slate-400 mt-1">Quản lý người dùng và phân quyền</p>
      </div>
      <button @click="openCreateModal" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2">
          <path d="M5 12h14" />
          <path d="M12 5v14" />
        </svg>
        Thêm người dùng
      </button>
    </div>

    <!-- Search -->
    <div class="bg-dark-800 rounded-xl border border-white/10 p-4 mb-6 flex-shrink-0">
      <div class="flex gap-4">
        <div class="relative flex-1">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" xmlns="http://www.w3.org/2000/svg"
            width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8" />
            <path d="m21 21-4.3-4.3" />
          </svg>
          <input :value="searchQuery" @input="setSearchQuery(($event.target as HTMLInputElement).value)"
            @keyup.enter="store.fetchUsers({ search: searchQuery })" type="text" class="form-input pl-10"
            placeholder="Tìm kiếm người dùng..." />
        </div>
        <button @click="store.fetchUsers({ search: searchQuery })" class="btn btn-secondary">Tìm kiếm</button>
      </div>
    </div>

    <!-- Table Container -->
    <div class="flex-1 min-h-0 bg-dark-800 rounded-xl border border-white/10 overflow-hidden flex flex-col">
      <div v-if="store.isLoading" class="flex-1 flex items-center justify-center">
        <div class="inline-block w-8 h-8 border-4 border-primary/20 border-t-primary rounded-full animate-spin"></div>
      </div>

      <div v-else class="flex-1 overflow-auto">
        <table class="w-full">
          <thead class="sticky top-0 z-10 bg-dark-700">
            <tr class="border-b border-white/10">
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">ID</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Người dùng</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Vai trò</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Trạng thái</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Ngày tạo</th>
              <th class="px-6 py-4 text-right text-sm font-semibold text-slate-400">Thao tác</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5">
            <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-white/5 transition-colors">
              <td class="px-6 py-4 text-sm text-slate-400">#{{ user.id }}</td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div
                    class="w-10 h-10 rounded-full bg-gradient-primary flex items-center justify-center text-white font-bold">
                    {{ user.name?.charAt(0)?.toUpperCase() }}
                  </div>
                  <div>
                    <p class="font-medium text-white">{{ user.name }}</p>
                    <p class="text-xs text-slate-500">{{ user.email }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="flex flex-wrap gap-1">
                  <span v-for="role in user.roles" :key="role.id" :class="['inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium',
                    role.name === 'admin' ? 'bg-error/10 text-error' :
                      role.name === 'manager' ? 'bg-warning/10 text-warning' :
                        role.name === 'staff' ? 'bg-info/10 text-info' :
                          'bg-primary/10 text-primary-light'
                  ]">
                    {{ role.name.charAt(0).toUpperCase() + role.name.slice(1) }}
                  </span>
                  <span v-if="!user.roles?.length" class="text-slate-500">Customer</span>
                </div>
              </td>
              <td class="px-6 py-4">
                <span
                  :class="['inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium', user.active !== false ? 'bg-success/10 text-success' : 'bg-error/10 text-error']">
                  {{ user.active !== false ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-slate-400">{{ formatDate(user.created_at) }}</td>
              <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button @click="openEditModal(user)"
                    class="w-8 h-8 rounded-lg bg-info/10 text-info hover:bg-info/20 flex items-center justify-center"
                    title="Sửa">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                      stroke="currentColor" stroke-width="2">
                      <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                      <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                    </svg>
                  </button>
                  <button @click="deleteUser(user.id)"
                    class="w-8 h-8 rounded-lg bg-error/10 text-error hover:bg-error/20 flex items-center justify-center"
                    title="Xóa">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                      stroke="currentColor" stroke-width="2">
                      <path d="M3 6h18" />
                      <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                      <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-if="!filteredUsers.length" class="py-16 text-center">
          <p class="text-slate-400">Chưa có người dùng nào</p>
        </div>

        <!-- Pagination -->
        <div v-if="store.totalPages > 1"
          class="sticky bottom-0 flex items-center justify-center gap-2 p-4 border-t border-white/10 bg-dark-800">
          <button @click="changePage(store.currentPage - 1)" :disabled="store.currentPage <= 1"
            class="btn btn-secondary btn-sm" :class="{ 'opacity-50 cursor-not-allowed': store.currentPage <= 1 }">
            {{ t('common.previous') }}
          </button>
          <span class="text-slate-400 text-sm">{{ store.currentPage }} / {{ store.totalPages }}</span>
          <button @click="changePage(store.currentPage + 1)" :disabled="store.currentPage >= store.totalPages"
            class="btn btn-secondary btn-sm"
            :class="{ 'opacity-50 cursor-not-allowed': store.currentPage >= store.totalPages }">
            {{ t('common.next') }}
          </button>
        </div>
      </div>
    </div>

    <!-- User Modal -->
    <BaseModal v-model="showModal" :title="editingUser ? 'Sửa người dùng' : 'Thêm người dùng'" size="md">
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Họ tên *</label>
          <input v-model="store.userForm.name" type="text" class="form-input" placeholder="Nhập họ tên" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Email *</label>
          <input v-model="store.userForm.email" type="email" class="form-input" placeholder="Nhập email" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Số điện thoại</label>
          <input v-model="store.userForm.phone" type="tel" class="form-input" placeholder="Nhập số điện thoại" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">
            Mật khẩu {{ editingUser ? '(để trống nếu không đổi)' : '*' }}
          </label>
          <input v-model="store.userForm.password" type="password" class="form-input" placeholder="Nhập mật khẩu" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Vai trò</label>
          <select v-model="store.userForm.role" class="form-input">
            <option v-for="role in store.roles" :key="role.id" :value="role.name">
              {{ role.name.charAt(0).toUpperCase() + role.name.slice(1) }}
            </option>
          </select>
        </div>
        <div>
          <label class="flex items-center gap-2">
            <input v-model="store.userForm.is_active" type="checkbox" class="form-checkbox" />
            <span class="text-sm text-slate-300">Kích hoạt</span>
          </label>
        </div>
        <div class="flex gap-3 pt-4">
          <button @click="showModal = false" class="btn btn-secondary flex-1">Hủy</button>
          <button @click="saveUser" :disabled="store.isSaving" class="btn btn-primary flex-1">
            {{ store.isSaving ? 'Đang lưu...' : 'Lưu' }}
          </button>
        </div>
      </div>
    </BaseModal>
  </div>
</template>
