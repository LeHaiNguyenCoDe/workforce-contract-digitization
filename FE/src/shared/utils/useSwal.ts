import Swal from 'sweetalert2'

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
 * Composable for SweetAlert2
 * Usage:
 * const swal = useSwal()
 * await swal.success('Thành công!')
 * await swal.error('Có lỗi xảy ra!')
 * const result = await swal.confirm('Bạn có chắc chắn?')
 */
export function useSwal() {
    const defaultOptions: Partial<SwalOptions> = {
        confirmButtonText: 'Xác nhận',
        cancelButtonText: 'Hủy',
        confirmButtonColor: '#3b82f6',
        cancelButtonColor: '#6b7280',
    }

    /**
     * Show success alert
     */
    const success = async (message: string, title: string = 'Thành công!') => {
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
    const error = async (message: string, title: string = 'Lỗi!') => {
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
    const warning = async (message: string, title: string = 'Cảnh báo!') => {
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
    const info = async (message: string, title: string = 'Thông tin') => {
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
        title: string = 'Xác nhận',
        confirmText: string = 'Xác nhận',
        cancelText: string = 'Hủy'
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
        message: string = 'Bạn có chắc chắn muốn xóa?',
        title: string = 'Xác nhận xóa'
    ): Promise<boolean> => {
        return await confirm(message, title, 'Xóa', 'Hủy')
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
    const loading = (message: string = 'Đang xử lý...') => {
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

