import type { RouteRecordRaw } from 'vue-router'

export const routes: RouteRecordRaw[] = [
    {
        path: 'warehouse',
        name: 'admin-warehouse-dashboard',
        component: () => import('../views/dashboard/WarehouseDashboard.vue'),
        meta: { layout: 'admin', title: 'Kho hàng' }
    },
    {
        path: 'warehouse/list',
        name: 'admin-warehouse-list',
        component: () => import('../views/management/WarehouseListView.vue'),
        meta: { layout: 'admin', title: 'Danh sách kho' }
    },
    {
        path: 'warehouse/inventory',
        name: 'admin-warehouse-inventory',
        component: () => import('../views/inventory/InventoryView.vue'),
        meta: { layout: 'admin', title: 'Tồn kho' }
    },
    {
        path: 'warehouse/products',
        name: 'admin-warehouse-products',
        component: () => import('../views/inventory/WarehouseProductsView.vue'),
        meta: { layout: 'admin', title: 'Sản phẩm kho' }
    },
    {
        path: 'warehouse/logs',
        name: 'admin-warehouse-logs',
        component: () => import('../views/inventory/InventoryLogsView.vue'),
        meta: { layout: 'admin', title: 'Lịch sử kho' }
    },
    {
        path: 'warehouse/inbound-receipts',
        name: 'admin-warehouse-inbound-receipts',
        component: () => import('../views/inbound/InboundReceiptView.vue'),
        meta: { layout: 'admin', title: 'Phiếu nhập kho' }
    },
    {
        path: 'warehouse/inbound-batches',
        name: 'admin-warehouse-inbound-batches',
        component: () => import('../views/inbound/InboundBatchView.vue'),
        meta: { layout: 'admin', title: 'Lô nhập kho' }
    },
    {
        path: 'warehouse/quality-checks',
        name: 'admin-warehouse-quality-checks',
        component: () => import('../views/inbound/QualityCheckView.vue'),
        meta: { layout: 'admin', title: 'Kiểm tra chất lượng' }
    },
    {
        path: 'warehouse/outbound-receipts',
        name: 'admin-warehouse-outbound-receipts',
        component: () => import('../views/outbound/OutboundReceiptView.vue'),
        meta: { layout: 'admin', title: 'Phiếu xuất kho' }
    },
    {
        path: 'warehouse/adjustments',
        name: 'admin-warehouse-adjustments',
        component: () => import('../views/outbound/StockAdjustmentView.vue'),
        meta: { layout: 'admin', title: 'Điều chỉnh kho' }
    },
    {
        path: 'warehouse/suppliers',
        name: 'admin-warehouse-suppliers',
        component: () => import('../views/management/SuppliersView.vue'),
        meta: { layout: 'admin', title: 'Nhà cung cấp' }
    }
]

export default routes
