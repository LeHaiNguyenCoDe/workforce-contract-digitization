<script setup lang="ts">

const { roles, selectedRole, isLoading, isSaving, permissionGroups, selectRole, hasPermission, togglePermission, savePermissions } = usePermissions()
</script>

<template>
  <div class="h-full flex flex-col p-6">
    <!-- Header -->
    <AdminPageHeader title="Quản lý Phân Quyền" description="Thiết lập quyền hạn chi tiết cho từng vai trò người dùng trong hệ thống">
      <template #actions>
        <DButton variant="primary" :loading="isSaving" @click="savePermissions">
          <img src="@/assets/admin/icons/save.svg" class="w-4.5 h-4.5 mr-2 brightness-0 invert" alt="Save" />
          Lưu cấu hình
        </DButton>
      </template>
    </AdminPageHeader>

    <!-- Content -->
    <div v-if="isLoading" class="flex-1 flex items-center justify-center">
      <div class="inline-block w-8 h-8 border-4 border-primary/20 border-t-primary rounded-full animate-spin"></div>
    </div>

    <div v-else class="flex gap-6 flex-1 min-h-0 animate-fade-in">
      <!-- Roles List -->
      <DCard class="w-72 flex-shrink-0 flex flex-col !bg-dark-800/50 border-white/5">
        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-4">Danh sách vai trò</p>
        <div class="space-y-1 overflow-auto flex-1 pr-1">
          <button v-for="role in roles" :key="role.id" @click="selectRole(role)"
            :class="['w-full px-4 py-3 rounded-xl text-left transition-all duration-200 group flex items-center justify-between', 
              selectedRole?.id === role.id 
                ? 'bg-primary text-white shadow-lg shadow-primary/20' 
                : 'text-slate-400 hover:bg-white/5 hover:text-white']">
            <span class="font-bold tracking-tight">{{ role.display_name }}</span>
            <img src="@/assets/admin/icons/chevron-right.svg" class="w-4 h-4 brightness-0 invert transition-all" 
              :class="[selectedRole?.id === role.id ? 'translate-x-0 opacity-100' : '-translate-x-2 opacity-0 group-hover:translate-x-0 group-hover:opacity-100']" alt="Select" />
          </button>
        </div>
      </DCard>

      <!-- Permissions Matrix -->
      <DCard class="flex-1 flex flex-col overflow-hidden !bg-dark-800 border-white/10" no-padding>
        <div v-if="selectedRole" class="flex flex-col h-full">
          <div class="px-6 py-4 border-b border-white/5 bg-dark-700/30 flex items-center justify-between">
            <div>
              <h3 class="text-lg font-bold text-white">{{ selectedRole.display_name }}</h3>
              <p class="text-[10px] text-slate-500 font-mono uppercase tracking-tighter">Role ID: #{{ selectedRole.id }}</p>
            </div>
            <span class="px-2 py-1 rounded text-[10px] bg-primary/10 text-primary-light font-bold">Cấp quyền</span>
          </div>

          <div class="flex-1 overflow-auto p-6 space-y-8">
            <div v-for="group in permissionGroups" :key="group.name" class="animate-fade-in-up">
              <div class="flex items-center gap-3 mb-4">
                <div class="h-px flex-1 bg-gradient-to-r from-white/10 to-transparent"></div>
                <h4 class="text-sm font-bold text-slate-400 uppercase tracking-widest">{{ group.label }}</h4>
                <div class="h-px flex-1 bg-gradient-to-l from-white/10 to-transparent"></div>
              </div>
              
              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                <label v-for="perm in group.permissions" :key="perm" 
                  class="group flex items-center gap-3 p-3 rounded-xl cursor-pointer transition-all border border-white/5 bg-dark-700/50 hover:bg-dark-600 hover:border-primary/30">
                  <div class="relative w-5 h-5 flex-shrink-0">
                    <input type="checkbox" :checked="hasPermission(perm)" @change="togglePermission(perm)"
                      class="peer absolute inset-0 opacity-0 cursor-pointer z-10" />
                    <div class="absolute inset-0 rounded bg-dark-500 border-2 border-white/10 transition-all peer-checked:bg-primary peer-checked:border-primary"></div>
                    <svg class="absolute inset-0.5 text-white opacity-0 transition-opacity peer-checked:opacity-100" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4">
                      <polyline points="20 6 9 17 4 12"/>
                    </svg>
                  </div>
                  <span class="text-sm text-slate-300 font-medium group-hover:text-white transition-colors capitalize">{{ perm.replace(/_/g, ' ') }}</span>
                </label>
              </div>
            </div>
          </div>
        </div>
        
        <div v-else class="flex-1 flex flex-col items-center justify-center p-12 text-center">
          <div class="w-16 h-16 rounded-full bg-dark-700 flex items-center justify-center text-slate-500 mb-4 border border-white/5">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
              <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
          </div>
          <h3 class="text-white font-bold text-lg">Chưa chọn vai trò</h3>
          <p class="text-slate-500 text-sm max-w-xs mt-1">Vui lòng chọn một vai trò từ danh sách bên trái để thiết lập quyền hạn.</p>
        </div>
      </DCard>
    </div>
  </div>
</template>
