/**
 * Auth Models
 */

export interface LoginCredentials {
    email: string
    password: string
    remember?: boolean
}

export interface RegisterData {
    name: string
    email: string
    phone?: string
    password: string
    password_confirmation: string
}

export interface UserModel {
    id: number
    name: string
    email: string
    phone?: string
    avatar?: string
    role: string
    created_at: string
}
