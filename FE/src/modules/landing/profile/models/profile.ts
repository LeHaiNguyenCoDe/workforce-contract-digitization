/**
 * Profile Models
 */

export interface ProfileModel {
    id: number
    name: string
    email: string
    phone?: string
    avatar?: string
    date_of_birth?: string
    gender?: 'male' | 'female' | 'other'
}

export interface AddressModel {
    id: number
    name: string
    phone: string
    address: string
    province: string
    district: string
    ward: string
    is_default: boolean
}

export interface PasswordChangeModel {
    current_password: string
    new_password: string
    new_password_confirmation: string
}
