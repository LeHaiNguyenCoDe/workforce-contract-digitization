/**
 * Status helpers for Admin views with i18n support
 */
import { useI18n } from 'vue-i18n'

// Return/RMA Status
export const returnStatusKeys: Record<string, string> = {
    pending: 'common.pendingApproval',
    approved: 'common.approved',
    rejected: 'common.rejected',
    receiving: 'common.receiving',
    completed: 'common.completed',
    cancelled: 'common.cancelled'
}

export const returnStatusClasses: Record<string, string> = {
    pending: 'bg-warning/10 text-warning',
    approved: 'bg-info/10 text-info',
    rejected: 'bg-error/10 text-error',
    receiving: 'bg-primary/10 text-primary',
    completed: 'bg-success/10 text-success',
    cancelled: 'bg-slate-500/10 text-slate-400'
}

export function useReturnStatusLabel() {
    const { t } = useI18n()
    return (status: string): string => {
        return returnStatusKeys[status] ? t(returnStatusKeys[status]) : status
    }
}

export function getReturnStatusClass(status: string): string {
    return returnStatusClasses[status] || 'bg-slate-500/10 text-slate-400'
}

// Order Status
export const orderStatusKeys: Record<string, string> = {
    pending: 'common.pending',
    processing: 'common.processingOrder',
    shipped: 'common.shipped',
    delivered: 'common.delivered',
    cancelled: 'common.cancelled'
}

export const orderStatusClasses: Record<string, string> = {
    pending: 'bg-warning/10 text-warning',
    processing: 'bg-info/10 text-info',
    shipped: 'bg-primary/10 text-primary',
    delivered: 'bg-success/10 text-success',
    cancelled: 'bg-error/10 text-error'
}

export function useOrderStatusLabel() {
    const { t } = useI18n()
    return (status: string): string => {
        return orderStatusKeys[status] ? t(orderStatusKeys[status]) : status
    }
}

export function getOrderStatusClass(status: string): string {
    return orderStatusClasses[status] || 'bg-slate-500/10 text-slate-400'
}

// User Status
export const userStatusKeys: Record<string, string> = {
    active: 'common.active',
    inactive: 'common.inactive',
    banned: 'common.banned'
}

export const userStatusClasses: Record<string, string> = {
    active: 'bg-success/10 text-success',
    inactive: 'bg-slate-500/10 text-slate-400',
    banned: 'bg-error/10 text-error'
}

export function useUserStatusLabel() {
    const { t } = useI18n()
    return (status: string): string => {
        return userStatusKeys[status] ? t(userStatusKeys[status]) : status
    }
}

// Point Transaction Type
export const pointTransactionKeys: Record<string, string> = {
    earn: 'common.earnPoints',
    redeem: 'common.redeemPoints',
    adjust: 'common.adjustPoints',
    expire: 'common.expirePoints'
}

export const pointTransactionClasses: Record<string, string> = {
    earn: 'bg-success/10 text-success',
    redeem: 'bg-warning/10 text-warning',
    adjust: 'bg-info/10 text-info',
    expire: 'bg-error/10 text-error'
}

export function usePointTransactionLabel() {
    const { t } = useI18n()
    return (type: string): string => {
        return pointTransactionKeys[type] ? t(pointTransactionKeys[type]) : type
    }
}

// Audit Log Actions
export const auditActionKeys: Record<string, string> = {
    create: 'common.create',
    update: 'common.update',
    delete: 'common.delete',
    login: 'common.login',
    logout: 'common.logout'
}

export const auditActionClasses: Record<string, string> = {
    create: 'bg-success/10 text-success',
    update: 'bg-info/10 text-info',
    delete: 'bg-error/10 text-error',
    login: 'bg-primary/10 text-primary',
    logout: 'bg-slate-500/10 text-slate-400'
}

export function useAuditActionLabel() {
    const { t } = useI18n()
    return (action: string): string => {
        return auditActionKeys[action] ? t(auditActionKeys[action]) : action
    }
}

// Legacy exports for backward compatibility
export const returnStatusLabels = returnStatusKeys
export const orderStatusLabels = orderStatusKeys
export const userStatusLabels = userStatusKeys
export const pointTransactionLabels = pointTransactionKeys
export const auditActionLabels = auditActionKeys

// Legacy functions - these will return translation keys, caller needs to translate
export function getReturnStatusLabel(status: string): string {
    return returnStatusKeys[status] || status
}

export function getOrderStatusLabel(status: string): string {
    return orderStatusKeys[status] || status
}
