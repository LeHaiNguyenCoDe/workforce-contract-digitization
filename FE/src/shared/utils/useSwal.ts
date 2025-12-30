import Swal from 'sweetalert2'
import { useI18n } from 'vue-i18n'

export interface SwalOptions {
    title?: string
    text?: string
    icon?: 'success' | 'error' | 'warning' | 'info' | 'question'
    confirmButtonText?: string
    cancelButtonText?: string
    showCancelButton?: boolean
    confirmButtonColor?: string
    cancelButtonColor?: string
    timer?: number
    timerProgressBar?: boolean
}

/**
 * Composable for SweetAlert2 with i18n support
 * Usage:
 * const swal = useSwal()
 * await swal.success('Thành công!')
 * await swal.error('Có lỗi xảy ra!')
 * const result = await swal.confirm('Bạn có chắc chắn?')
 */
export function useSwal() {
    const { t } = useI18n()
    
    const defaultOptions: Partial<SwalOptions> = {
        confirmButtonText: t('common.confirmAction'),
        cancelButtonText: t('common.cancel'),
        confirmButtonColor: '#3b82f6',
        cancelButtonColor: '#6b7280',
    }

    /**
     * Show success alert
     */
    const success = async (message: string, title: string = t('common.success')) => {
        return await Swal.fire({
            ...defaultOptions,
            title,
            text: message,
            icon: 'success',
            showCancelButton: false,
        })
    }

    /**
     * Show error alert
     */
    const error = async (message: string, title: string = t('common.error')) => {
        return await Swal.fire({
            ...defaultOptions,
            title,
            text: message,
            icon: 'error',
            showCancelButton: false,
        })
    }

    /**
     * Show warning alert
     */
    const warning = async (message: string, title: string = t('common.warning')) => {
        return await Swal.fire({
            ...defaultOptions,
            title,
            text: message,
            icon: 'warning',
            showCancelButton: false,
        })
    }

    /**
     * Show info alert
     */
    const info = async (message: string, title: string = t('common.info')) => {
        return await Swal.fire({
            ...defaultOptions,
            title,
            text: message,
            icon: 'info',
            showCancelButton: false,
        })
    }

    /**
     * Show confirmation dialog
     * Returns true if confirmed, false if cancelled
     */
    const confirm = async (
        message: string,
        title: string = t('common.confirmAction'),
        confirmText: string = t('common.confirmAction'),
        cancelText: string = t('common.cancel')
    ): Promise<boolean> => {
        const result = await Swal.fire({
            ...defaultOptions,
            title,
            text: message,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: confirmText,
            cancelButtonText: cancelText,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
        })

        return result.isConfirmed
    }

    /**
     * Show delete confirmation
     */
    const confirmDelete = async (
        message: string = t('common.confirmDeleteMessage'),
        title: string = t('common.confirmDeleteTitle')
    ): Promise<boolean> => {
        return await confirm(message, title, t('common.delete'), t('common.cancel'))
    }

    /**
     * Show custom alert with options
     */
    const fire = async (options: SwalOptions) => {
        return await Swal.fire({
            ...defaultOptions,
            ...options,
        })
    }

    /**
     * Show loading alert
     */
    const loading = (message: string = t('common.processing')) => {
        Swal.fire({
            title: message,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading()
            },
        })
    }

    /**
     * Close current alert
     */
    const close = () => {
        Swal.close()
    }

    return {
        success,
        error,
        warning,
        info,
        confirm,
        confirmDelete,
        fire,
        loading,
        close,
    }
}

