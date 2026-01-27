<script setup lang="ts">
import { ref, onMounted } from 'vue'

interface Role {
  id: string
  name: string
  description: string
  users_count: number
}

interface Permission {
  id: string
  name: string
  module: string
}

// State
const roles = ref<Role[]>([])
const permissions = ref<Permission[]>([])
const rolePermissions = ref<Record<string, string[]>>({})
const isLoading = ref(true)
const selectedRole = ref<string>('')

// Modules
const modules = [
  { id: 'products', name: 'Sản phẩm', icon: '📦' },
  { id: 'orders', name: 'Đơn hàng', icon: '🛒' },
  { id: 'customers', name: 'Khách hàng', icon: '👥' },
  { id: 'warehouse', name: 'Kho hàng', icon: '🏭' },
  { id: 'finance', name: 'Tài chính', icon: '💰' },
  { id: 'reports', name: 'Báo cáo', icon: '📊' },
  { id: 'marketing', name: 'Marketing', icon: '📢' },
  { id: 'settings', name: 'Cài đặt', icon: '⚙️' }
]

// Methods
const fetchData = async () => {
  isLoading.value = true
  setTimeout(() => {
    roles.value = [
      { id: 'admin', name: 'Admin', description: 'Toàn quyền quản trị hệ thống', users_count: 2 },
      { id: 'manager', name: 'Manager', description: 'Quản lý vận hành và nhân viên', users_count: 5 },
      { id: 'staff', name: 'Staff', description: 'Nhân viên xử lý đơn hàng', users_count: 12 },
      { id: 'warehouse', name: 'Kho', description: 'Nhân viên kho hàng', users_count: 4 }
    ]

    permissions.value = modules.flatMap(m => [
      { id: `${m.id}.view`, name: 'Xem', module: m.id },
      { id: `${m.id}.create`, name: 'Tạo', module: m.id },
      { id: `${m.id}.edit`, name: 'Sửa', module: m.id },
      { id: `${m.id}.delete`, name: 'Xóa', module: m.id }
    ])

    rolePermissions.value = {
      admin: permissions.value.map(p => p.id),
      manager: permissions.value.filter(p => !p.id.includes('settings.delete')).map(p => p.id),
      staff: permissions.value.filter(p => 
        (p.module === 'orders' || p.module === 'customers') && 
        (p.name === 'Xem' || p.name === 'Sửa')
      ).map(p => p.id),
      warehouse: permissions.value.filter(p => 
        p.module === 'warehouse' || 
        (p.module === 'products' && p.name === 'Xem')
      ).map(p => p.id)
    }

    selectedRole.value = 'admin'
    isLoading.value = false
  }, 500)
}

const hasPermission = (roleId: string, permissionId: string) => {
  return rolePermissions.value[roleId]?.includes(permissionId)
}

const togglePermission = (roleId: string, permissionId: string) => {
  if (roleId === 'admin') return // Admin cannot be modified

  if (!rolePermissions.value[roleId]) {
    rolePermissions.value[roleId] = []
  }

  const index = rolePermissions.value[roleId].indexOf(permissionId)
  if (index === -1) {
    rolePermissions.value[roleId].push(permissionId)
  } else {
    rolePermissions.value[roleId].splice(index, 1)
  }
}

const toggleModuleAll = (roleId: string, moduleId: string) => {
  if (roleId === 'admin') return

  const modulePerms = permissions.value.filter(p => p.module === moduleId).map(p => p.id)
  const allHave = modulePerms.every(p => hasPermission(roleId, p))

  if (allHave) {
    // Remove all
    rolePermissions.value[roleId] = rolePermissions.value[roleId].filter(p => !modulePerms.includes(p))
  } else {
    // Add all
    modulePerms.forEach(p => {
      if (!rolePermissions.value[roleId].includes(p)) {
        rolePermissions.value[roleId].push(p)
      }
    })
  }
}

const getRoleColor = (roleId: string) => {
  const colors: Record<string, string> = {
    admin: 'from-red-500 to-orange-500',
    manager: 'from-blue-500 to-cyan-500',
    staff: 'from-green-500 to-emerald-500',
    warehouse: 'from-purple-500 to-pink-500'
  }
  return colors[roleId] || 'from-slate-500 to-slate-600'
}

// Lifecycle
onMounted(() => {
  fetchData()
})
</script>

<template>
  <div class="h-full flex flex-col p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6 flex-shrink-0">
      <div>
        <h1 class="text-2xl font-bold text-white">Phân quyền</h1>
        <p class="text-slate-400 mt-1">Quản lý quyền truy cập cho các vai trò</p>
      </div>
    </div>

    <!-- Info Banner -->
    <div class="bg-warning/10 border border-warning/20 rounded-xl p-4 mb-6 flex-shrink-0">
      <div class="flex gap-3">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2" class="text-warning flex-shrink-0">
          <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" />
          <path d="M12 9v4" />
          <path d="M12 17h.01" />
        </svg>
        <div>
          <p class="text-warning font-medium">Chức năng demo</p>
          <p class="text-sm text-slate-400 mt-1">
            Hệ thống phân quyền đang trong quá trình phát triển. Các thay đổi hiện tại chỉ lưu tạm thời và 
            chưa áp dụng vào hệ thống thực tế.
          </p>
        </div>
      </div>
    </div>

    <div v-if="isLoading" class="flex-1 flex items-center justify-center">
      <div class="w-8 h-8 border-4 border-primary/20 border-t-primary rounded-full animate-spin"></div>
    </div>

    <div v-else class="flex-1 min-h-0 flex gap-6">
      <!-- Roles Sidebar -->
      <div class="w-64 flex-shrink-0 space-y-2">
        <h3 class="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-3">Vai trò</h3>
        <button v-for="role in roles" :key="role.id"
          @click="selectedRole = role.id"
          class="w-full text-left p-4 rounded-xl border transition-all"
          :class="selectedRole === role.id 
            ? 'bg-dark-700 border-primary/50' 
            : 'bg-dark-800 border-white/10 hover:border-white/20'">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-gradient-to-br flex items-center justify-center text-white font-semibold"
              :class="getRoleColor(role.id)">
              {{ role.name.charAt(0) }}
            </div>
            <div class="flex-1">
              <p class="font-medium text-white">{{ role.name }}</p>
              <p class="text-xs text-slate-500">{{ role.users_count }} người dùng</p>
            </div>
          </div>
        </button>
      </div>

      <!-- Permissions Matrix -->
      <div class="flex-1 bg-dark-800 rounded-xl border border-white/10 overflow-hidden flex flex-col">
        <div class="p-4 border-b border-white/10">
          <h3 class="text-lg font-semibold text-white">
            Quyền của {{ roles.find(r => r.id === selectedRole)?.name }}
          </h3>
          <p class="text-sm text-slate-400">{{ roles.find(r => r.id === selectedRole)?.description }}</p>
        </div>

        <div class="flex-1 overflow-auto">
          <table class="w-full">
            <thead class="sticky top-0 bg-dark-700">
              <tr>
                <th class="px-6 py-3 text-left text-sm font-semibold text-slate-400">Module</th>
                <th class="px-4 py-3 text-center text-sm font-semibold text-slate-400 w-20">Xem</th>
                <th class="px-4 py-3 text-center text-sm font-semibold text-slate-400 w-20">Tạo</th>
                <th class="px-4 py-3 text-center text-sm font-semibold text-slate-400 w-20">Sửa</th>
                <th class="px-4 py-3 text-center text-sm font-semibold text-slate-400 w-20">Xóa</th>
                <th class="px-4 py-3 text-center text-sm font-semibold text-slate-400 w-24">Tất cả</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
              <tr v-for="mod in modules" :key="mod.id" class="hover:bg-white/5">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <span class="text-xl">{{ mod.icon }}</span>
                    <span class="font-medium text-white">{{ mod.name }}</span>
                  </div>
                </td>
                <td v-for="action in ['view', 'create', 'edit', 'delete']" :key="action" class="px-4 py-4 text-center">
                  <button @click="togglePermission(selectedRole, `${mod.id}.${action}`)"
                    :disabled="selectedRole === 'admin'"
                    class="w-6 h-6 rounded border-2 transition-all mx-auto flex items-center justify-center"
                    :class="hasPermission(selectedRole, `${mod.id}.${action}`)
                      ? 'bg-success border-success text-white'
                      : 'border-slate-600 hover:border-slate-500'">
                    <svg v-if="hasPermission(selectedRole, `${mod.id}.${action}`)"
                      xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                      stroke="currentColor" stroke-width="3">
                      <path d="M20 6 9 17l-5-5" />
                    </svg>
                  </button>
                </td>
                <td class="px-4 py-4 text-center">
                  <button @click="toggleModuleAll(selectedRole, mod.id)"
                    :disabled="selectedRole === 'admin'"
                    class="text-xs px-2 py-1 rounded bg-slate-700 text-slate-300 hover:bg-slate-600 transition-colors">
                    Toggle
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="p-4 border-t border-white/10 flex justify-end">
          <button class="btn btn-primary" :disabled="selectedRole === 'admin'">
            Lưu thay đổi
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
