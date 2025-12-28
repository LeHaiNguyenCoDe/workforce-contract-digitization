<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import type { User } from '../../store/store'
import { useSwal } from '@/shared/utils'
import { userColumns } from '../../configs/columns'

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
    <AdminPageHeader :title="t('admin.users')" description="Quản lý tài khoản người dùng và phân quyền hệ thống">
      <template #actions>
        <DButton variant="primary" @click="openCreateModal">
          <img src="@/assets/admin/icons/plus.svg" class="w-5 h-5 mr-2 brightness-0 invert" alt="Add" />
          Thêm người dùng
        </DButton>
      </template>
    </AdminPageHeader>

    <!-- Search -->
    <AdminSearch :modelValue="searchQuery" @update:modelValue="setSearchQuery" @search="store.fetchUsers({ search: searchQuery })" placeholder="Tìm kiếm theo tên hoặc email..." />

    <!-- Table -->
    <AdminTable :columns="userColumns" :data="filteredUsers" :loading="store.isLoading" empty-text="Chưa có người dùng nào">
      <template #cell-user="{ item }">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-full bg-gradient-to-br from-primary to-primary-dark flex items-center justify-center text-white font-bold text-sm shadow-sm">
            {{ item.name?.charAt(0)?.toUpperCase() }}
          </div>
          <div>
            <p class="font-medium text-white">{{ item.name }}</p>
            <p class="text-[10px] text-slate-500">{{ item.email }}</p>
          </div>
        </div>
      </template>

      <template #cell-roles="{ item }">
        <div class="flex flex-wrap gap-1">
          <template v-if="item.roles?.length">
            <span v-for="role in item.roles" :key="role.id" :class="['inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-tight',
              role.name === 'admin' ? 'bg-error/10 text-error' :
                role.name === 'manager' ? 'bg-warning/10 text-warning' :
                  role.name === 'staff' ? 'bg-info/10 text-info' :
                    'bg-primary/10 text-primary-light'
            ]">
              {{ role.name }}
            </span>
          </template>
          <span v-else class="text-[10px] text-slate-500 italic">Khách hàng</span>
        </div>
      </template>

      <template #cell-status="{ item }">
        <span :class="['px-2 py-0.5 rounded-full text-[10px] font-bold uppercase', item.active !== false ? 'bg-success/10 text-success' : 'bg-error/10 text-error']">
          {{ item.active !== false ? 'Hoạt động' : 'Khóa' }}
        </span>
      </template>

      <template #cell-created_at="{ value }">
        <span class="text-slate-400 text-xs">{{ formatDate(value) }}</span>
      </template>

      <template #actions="{ item }">
        <div class="flex items-center justify-end gap-1">
          <DAction icon="edit" @click.stop="openEditModal(item)" />
          <DAction icon="delete" variant="danger" @click.stop="deleteUser(item.id)" />
        </div>
      </template>

      <template #footer>
        <Pagination :currentPage="store.currentPage" :totalPages="store.totalPages" @page-change="changePage" />
      </template>
    </AdminTable>

    <!-- Modal -->
    <DModal v-model="showModal" :title="editingUser ? 'Cập nhật thông tin người dùng' : 'Tạo tài khoản mới'" size="md">
      <div class="grid grid-cols-2 gap-4">
        <div class="col-span-2">
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
        <div class="col-span-2">
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
        <div class="flex items-end pb-3">
          <label class="flex items-center gap-2 cursor-pointer">
            <input v-model="store.userForm.is_active" type="checkbox" class="form-checkbox" />
            <span class="text-sm text-slate-300">Kích hoạt tài khoản</span>
          </label>
        </div>
      </div>
      <template #footer>
        <div class="flex gap-3">
          <DButton variant="secondary" class="flex-1" @click="showModal = false">Hủy</DButton>
          <DButton variant="primary" class="flex-1" :loading="store.isSaving" @click="saveUser">Lưu thông tin</DButton>
        </div>
      </template>
    </DModal>
  </div>
</template>
