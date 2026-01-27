/**
 * User Model
 */
export interface User {
    id: number
    name: string
    email: string
    phone?: string
    role: 'admin' | 'manager' | 'staff' | 'customer'
    active: boolean
    avatar?: string
    created_at: string
    updated_at: string
}

export interface CreateUserPayload {
    name: string
    email: string
    password: string
    phone?: string
    role: string
}
