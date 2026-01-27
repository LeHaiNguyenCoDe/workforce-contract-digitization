import httpClient from '../httpClient'
import type { ApiResponse } from '../types'

// Settings Types
export interface GeneralSettings {
    store_name: string
    store_email: string
    store_phone: string
    store_address: string
    store_logo: string
    currency: string
    timezone: string
}

export interface PaymentSettings {
    cod_enabled: boolean
    bank_transfer_enabled: boolean
    vnpay_enabled: boolean
    momo_enabled: boolean
    bank_name: string
    bank_account: string
    bank_owner: string
}

export interface ShippingSettings {
    free_shipping_threshold: number
    default_shipping_fee: number
    ghn_enabled: boolean
    ghtk_enabled: boolean
}

export interface SeoSettings {
    meta_title: string
    meta_description: string
    meta_keywords: string
    google_analytics: string
    facebook_pixel: string
}

export interface AllSettings {
    general?: GeneralSettings
    payment?: PaymentSettings
    shipping?: ShippingSettings
    seo?: SeoSettings
}

class SettingsService {
    protected readonly endpoint = 'admin/settings'

    /**
     * Get all settings grouped by section
     */
    async getAll(): Promise<AllSettings> {
        const response = await httpClient.get<ApiResponse<AllSettings>>(this.endpoint)
        return response.data.data || {}
    }

    /**
     * Get settings by group
     */
    async getByGroup<T>(group: 'general' | 'payment' | 'shipping' | 'seo'): Promise<T> {
        const response = await httpClient.get<ApiResponse<T>>(`${this.endpoint}/${group}`)
        return response.data.data!
    }

    /**
     * Update settings for a group
     */
    async update<T>(group: 'general' | 'payment' | 'shipping' | 'seo', data: Partial<T>): Promise<void> {
        await httpClient.put(`${this.endpoint}/${group}`, data)
    }

    /**
     * Upload logo and update general settings
     */
    async uploadLogo(file: File): Promise<string> {
        const formData = new FormData()
        formData.append('store_logo', file)
        
        const response = await httpClient.put<ApiResponse<{ store_logo: string }>>(
            `${this.endpoint}/general`,
            formData,
            { headers: { 'Content-Type': 'multipart/form-data' } }
        )
        return response.data.data?.store_logo || ''
    }
}

export const settingsService = new SettingsService()
