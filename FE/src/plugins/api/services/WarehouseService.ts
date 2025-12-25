import { BaseApiService } from './BaseApiService'
import httpClient from '../httpClient'
import type { ApiResponse } from '../types'

export interface Stock {
  id: number
  warehouse_id: number
  product_id: number
  product_variant_id?: number | null
  quantity: number
  available_quantity: number // BR-06.1: Tồn kho có thể xuất
  expiry_date?: string | null
  inbound_batch_id?: number | null
  quality_check_id?: number | null
  product?: any
  productVariant?: any
  warehouse?: any
  inboundBatch?: any
  qualityCheck?: any
}

export interface InventoryLog {
  id: number
  warehouse_id: number
  product_id: number
  product_variant_id?: number | null
  movement_type: 'inbound' | 'qc_pass' | 'qc_fail' | 'outbound' | 'adjust' | 'return'
  quantity: number
  quantity_before: number
  quantity_after: number
  user_id?: number | null
  inbound_batch_id?: number | null
  quality_check_id?: number | null
  reason?: string | null
  note?: string | null
  created_at: string
  product?: any
  user?: any
  inboundBatch?: any
  qualityCheck?: any
}

export interface InboundBatch {
  id: number
  batch_number: string
  warehouse_id: number
  supplier_id: number
  created_by: number
  status: 'pending' | 'received' | 'qc_in_progress' | 'qc_completed' | 'completed' | 'cancelled'
  received_date?: string | null
  notes?: string | null
  warehouse?: any
  supplier?: any
  items?: InboundBatchItem[]
  qualityCheck?: any
}

export interface InboundBatchItem {
  id: number
  inbound_batch_id: number
  product_id: number
  product_variant_id?: number | null
  quantity_received: number
  product?: any
  productVariant?: any
}

export interface QualityCheck {
  id: number
  inbound_batch_id: number
  warehouse_id: number
  product_id: number
  supplier_id: number
  inspector_id: number
  check_date: string
  status: 'pass' | 'fail' | 'partial'
  score: number
  quantity_passed: number
  quantity_failed: number
  notes?: string | null
  issues?: string[] | null
  is_rollback: boolean
  approved_by?: number | null
  approved_at?: string | null
  inboundBatch?: InboundBatch
  warehouse?: any
  product?: any
  supplier?: any
  inspector?: any
}

export interface UpdateStockRequest {
  product_id: number
  product_variant_id?: number | null
  quantity: number
  type: 'in' | 'out' | 'adjust'
  note?: string
}

export interface AdjustStockRequest {
  product_id: number
  product_variant_id?: number | null
  quantity: number
  available_quantity?: number
  reason: string // BR-05.2: Bắt buộc có lý do
  note?: string
}

export interface OutboundStockRequest {
  product_id: number
  product_variant_id?: number | null
  quantity: number
  reference_type?: string
  reference_id?: number
  note?: string
}

export interface CreateInboundBatchRequest {
  warehouse_id: number
  supplier_id: number
  items: Array<{
    product_id: number
    product_variant_id?: number | null
    quantity_received: number
  }>
  notes?: string
}

export interface ReceiveInboundBatchRequest {
  received_date?: string
  items?: Array<{
    product_id: number
    product_variant_id?: number | null
    quantity_received: number
  }>
  notes?: string
}

export interface CreateQualityCheckRequest {
  inbound_batch_id: number
  product_id: number
  check_date?: string
  status: 'pass' | 'fail' | 'partial'
  score?: number
  quantity_passed: number
  quantity_failed: number
  notes?: string
  issues?: string[]
}

export interface Warehouse {
  id: number
  name: string
  code?: string
  address?: string
  description?: string
}

/**
 * Warehouse Service
 * Handles warehouse and stock operations according to BRD
 */
class WarehouseService extends BaseApiService<Warehouse> {
  protected readonly endpoint = '/admin/warehouses'

  /**
   * Get all stocks for a warehouse (BR-06.1: Chỉ hiển thị Available Inventory)
   */
  async getStocks(warehouseId: number): Promise<Stock[]> {
    const response = await httpClient.get<ApiResponse<Stock[]>>(
      `${this.endpoint}/${warehouseId}/stocks`
    )
    return response.data.data || []
  }

  /**
   * BR-05.1, BR-05.2, BR-05.3: Điều chỉnh tồn kho
   */
  async adjustStock(warehouseId: number, data: AdjustStockRequest): Promise<Stock> {
    const response = await httpClient.post<ApiResponse<Stock>>(
      `${this.endpoint}/${warehouseId}/stocks/adjust`,
      data
    )
    return response.data.data!
  }

  /**
   * BR-06.1, BR-06.2, BR-06.3: Xuất kho
   */
  async outboundStock(warehouseId: number, data: OutboundStockRequest): Promise<Stock> {
    const response = await httpClient.post<ApiResponse<Stock>>(
      `${this.endpoint}/${warehouseId}/stocks/outbound`,
      data
    )
    return response.data.data!
  }

  /**
   * BR-02.1: Tạo Inbound Batch
   */
  async createInboundBatch(data: CreateInboundBatchRequest): Promise<InboundBatch> {
    const response = await httpClient.post<ApiResponse<InboundBatch>>(
      `${this.endpoint}/inbound-batches`,
      data
    )
    return response.data.data!
  }

  /**
   * Get inbound batches
   */
  async getInboundBatches(filters?: { status?: string; warehouse_id?: number }): Promise<InboundBatch[]> {
    const params = new URLSearchParams()
    if (filters?.status) params.set('status', filters.status)
    if (filters?.warehouse_id) params.set('warehouse_id', String(filters.warehouse_id))
    
    const query = params.toString()
    const url = `${this.endpoint}/inbound-batches${query ? `?${query}` : ''}`
    
    const response = await httpClient.get<ApiResponse<InboundBatch[]>>(url)
    return response.data.data || []
  }

  /**
   * Get inbound batch by ID
   */
  async getInboundBatch(id: number): Promise<InboundBatch> {
    const response = await httpClient.get<ApiResponse<InboundBatch>>(
      `${this.endpoint}/inbound-batches/${id}`
    )
    return response.data.data!
  }

  /**
   * BR-02.2: Nhận hàng (RECEIVED)
   */
  async receiveInboundBatch(batchId: number, data: ReceiveInboundBatchRequest): Promise<InboundBatch> {
    const response = await httpClient.post<ApiResponse<InboundBatch>>(
      `${this.endpoint}/inbound-batches/${batchId}/receive`,
      data
    )
    return response.data.data!
  }

  /**
   * BR-03.1: Tạo Quality Check (chỉ trên Batch)
   */
  async createQualityCheck(data: CreateQualityCheckRequest): Promise<QualityCheck> {
    const response = await httpClient.post<ApiResponse<QualityCheck>>(
      `${this.endpoint}/quality-checks`,
      data
    )
    return response.data.data!
  }

  /**
   * Get quality checks
   */
  async getQualityChecks(): Promise<QualityCheck[]> {
    const response = await httpClient.get<ApiResponse<QualityCheck[]>>(
      `${this.endpoint}/quality-checks`
    )
    return response.data.data || []
  }

  /**
   * BR-09.2: Get inventory logs
   */
  async getInventoryLogs(warehouseId: number, params?: { page?: number; per_page?: number }): Promise<{
    data: InventoryLog[]
    current_page: number
    last_page: number
    per_page: number
    total: number
  }> {
    const searchParams = new URLSearchParams()
    if (params?.page) searchParams.set('page', String(params.page))
    if (params?.per_page) searchParams.set('per_page', String(params.per_page))
    
    const query = searchParams.toString()
    const url = `${this.endpoint}/${warehouseId}/inventory-logs${query ? `?${query}` : ''}`
    
    const response = await httpClient.get<ApiResponse<{
      data: InventoryLog[]
      current_page: number
      last_page: number
      per_page: number
      total: number
    }>>(url)
    return response.data.data || { data: [], current_page: 1, last_page: 1, per_page: 20, total: 0 }
  }

  /**
   * Legacy: Update stock (deprecated, use adjustStock or outboundStock instead)
   */
  async updateStock(warehouseId: number, data: UpdateStockRequest): Promise<Stock> {
    const response = await httpClient.post<ApiResponse<Stock>>(
      `${this.endpoint}/${warehouseId}/stocks`,
      data
    )
    return response.data.data!
  }
}

// Export singleton instance
export const warehouseService = new WarehouseService()

