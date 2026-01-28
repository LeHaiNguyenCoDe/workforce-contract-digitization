/**
 * usePermissions Composable
 */
import { ref, onMounted } from 'vue'
import { useSwal, useErrorHandler } from '@/utils'
import type { Role } from '../models/permission'
import { permissionGroups } from '../models/permission'
import permissionService from '../services/permissionService'

export function usePermissions() {
    const swal = useSwal()
    const { handleError } = useErrorHandler()

    const roles = ref<Role[]>([])
    const selectedRole = ref<Role | null>(null)
    const isLoading = ref(false)
    const isSaving = ref(false)

    async function fetchRoles() {
        isLoading.value = true
        try {
            const data = await permissionService.getAllRoles()
            roles.value = data
            if (roles.value.length > 0 && !selectedRole.value) {
                selectedRole.value = { ...roles.value[0] }
            }
        } catch (error) {
            handleError(error, 'Không thể tải danh sách vai trò')
        } finally {
            isLoading.value = false
        }
    }

    function selectRole(role: Role) {
        selectedRole.value = { ...role }
    }

    function hasPermission(permission: string): boolean {
        return selectedRole.value?.permissions.includes(permission) || false
    }

    function togglePermission(permission: string) {
        if (!selectedRole.value) return
        const idx = selectedRole.value.permissions.indexOf(permission)
        if (idx > -1) {
            selectedRole.value.permissions.splice(idx, 1)
        } else {
            selectedRole.value.permissions.push(permission)
        }
    }

    async function savePermissions() {
        if (!selectedRole.value) return
        
        isSaving.value = true
        try {
            await permissionService.updateRolePermissions(selectedRole.value.id, selectedRole.value.permissions)
            await swal.success('Đã lưu quyền thành công!')
            await fetchRoles()
        } catch (error) {
            handleError(error, 'Không thể lưu quyền')
        } finally {
            isSaving.value = false
        }
    }

    onMounted(fetchRoles)

    return { roles, selectedRole, isLoading, isSaving, permissionGroups, fetchRoles, selectRole, hasPermission, togglePermission, savePermissions }
}
