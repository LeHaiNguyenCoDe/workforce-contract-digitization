/**
 * usePermission Composable
 * Check user permissions for showing/hiding UI elements
 */
import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'

export function usePermission() {
    const authStore = useAuthStore()

    /**
     * Check if current user has a specific permission
     */
    const hasPermission = (permission: string): boolean => {
        if (!authStore.user) return false
        
        // Admin role always has all permissions
        if (authStore.user.roles?.some(r => r.name === 'admin')) return true
        
        // Check permissions array from API
        return authStore.user.permissions?.includes(permission) ?? false
    }

    /**
     * Check if user has any of the given permissions
     */
    const hasAnyPermission = (permissions: string[]): boolean => {
        return permissions.some(p => hasPermission(p))
    }

    /**
     * Check if user has all of the given permissions
     */
    const hasAllPermissions = (permissions: string[]): boolean => {
        return permissions.every(p => hasPermission(p))
    }

    /**
     * Computed: Check if user is admin
     */
    const isAdmin = computed(() => {
        return authStore.user?.roles?.some(r => r.name === 'admin') ?? false
    })

    /**
     * Computed: Check if user is manager or admin
     */
    const isManager = computed(() => {
        return authStore.user?.roles?.some(r => 
            r.name === 'admin' || r.name === 'manager'
        ) ?? false
    })

    /**
     * Get current user's permissions
     */
    const permissions = computed(() => {
        return authStore.user?.permissions ?? []
    })

    return {
        hasPermission,
        hasAnyPermission,
        hasAllPermissions,
        isAdmin,
        isManager,
        permissions
    }
}
