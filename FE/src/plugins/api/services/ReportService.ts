import httpClient from '../httpClient'
import type { ApiResponse } from '../types'

// Dashboard Analytics Types
export interface DashboardKPIs {
    revenue: number
    gross_profit: number
    gross_margin: number
    order_count: number
    avg_order_value: number
    customer_count: number
    new_customers: number
}

export interface RevenueChartData {
    date: string
    count: number
    total: number
}

export interface OrderByStatus {
    status: string
    count: number
    total: number
}

export interface TopProduct {
    id: number
    name: string
    total_qty: number
    total_value: number
}

export interface RecentOrder {
    id: number
    order_number: string
    user_id: number
    total: number
    status: string
    created_at: string
    user?: {
        id: number
        name: string
        email: string
    }
}

export interface DashboardAlerts {
    low_stock: number
    pending_orders: number
    pending_returns: number
    pending_purchase_requests: number
}

export interface DashboardAnalytics {
    kpis: DashboardKPIs
    revenue_chart: RevenueChartData[]
    orders_by_status: OrderByStatus[]
    top_products: TopProduct[]
    recent_orders: RecentOrder[]
    alerts: DashboardAlerts
    period: {
        from: string
        to: string
    }
}

class ReportService {
    protected readonly endpoint = 'admin/reports'

    /**
     * Get dashboard analytics
     */
    async getDashboardAnalytics(params?: {
        from_date?: string
        to_date?: string
        warehouse_id?: number
    }): Promise<DashboardAnalytics> {
        const searchParams = new URLSearchParams()
        if (params?.from_date) searchParams.set('from_date', params.from_date)
        if (params?.to_date) searchParams.set('to_date', params.to_date)
        if (params?.warehouse_id) searchParams.set('warehouse_id', String(params.warehouse_id))

        const query = searchParams.toString()
        const url = `${this.endpoint}/dashboard${query ? `?${query}` : ''}`

        const response = await httpClient.get<ApiResponse<DashboardAnalytics>>(url)
        return response.data.data!
    }

    /**
     * Get P&L Report
     */
    async getPnLReport(params?: {
        from_date?: string
        to_date?: string
        warehouse_id?: number
    }): Promise<any> {
        const searchParams = new URLSearchParams()
        if (params?.from_date) searchParams.set('from_date', params.from_date)
        if (params?.to_date) searchParams.set('to_date', params.to_date)
        if (params?.warehouse_id) searchParams.set('warehouse_id', String(params.warehouse_id))

        const query = searchParams.toString()
        const url = `${this.endpoint}/pnl${query ? `?${query}` : ''}`

        const response = await httpClient.get<ApiResponse<any>>(url)
        return response.data.data
    }

    /**
     * Get Sales Report
     */
    async getSalesReport(params?: {
        from_date?: string
        to_date?: string
        warehouse_id?: number
    }): Promise<any> {
        const searchParams = new URLSearchParams()
        if (params?.from_date) searchParams.set('from_date', params.from_date)
        if (params?.to_date) searchParams.set('to_date', params.to_date)
        if (params?.warehouse_id) searchParams.set('warehouse_id', String(params.warehouse_id))

        const query = searchParams.toString()
        const url = `${this.endpoint}/sales${query ? `?${query}` : ''}`

        const response = await httpClient.get<ApiResponse<any>>(url)
        return response.data.data
    }

    /**
     * Get Inventory Report
     */
    async getInventoryReport(warehouseId?: number): Promise<any> {
        const url = warehouseId 
            ? `${this.endpoint}/inventory?warehouse_id=${warehouseId}`
            : `${this.endpoint}/inventory`

        const response = await httpClient.get<ApiResponse<any>>(url)
        return response.data.data
    }
}

export const reportService = new ReportService()
