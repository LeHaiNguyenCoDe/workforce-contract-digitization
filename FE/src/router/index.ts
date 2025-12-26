import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router'

// Define routes manually for reliability
const routes: RouteRecordRaw[] = [
  // Landing (Frontend) routes
  {
    path: '/',
    component: () => import('@/layouts/landing/LandingLayout.vue'),
    children: [
      {
        path: '',
        name: 'home',
        component: () => import('@/modules/landing/modules/home/views/HomeView.vue')
      },
      {
        path: 'login',
        name: 'login',
        component: () => import('@/modules/landing/modules/auth/views/LoginView.vue'),
        meta: { guest: true }
      },
      {
        path: 'register',
        name: 'register',
        component: () => import('@/modules/landing/modules/auth/views/RegisterView.vue'),
        meta: { guest: true }
      },
      {
        path: 'products',
        name: 'products',
        component: () => import('@/modules/landing/modules/products/views/ProductListView.vue')
      },
      {
        path: 'products/:id',
        name: 'product-detail',
        component: () => import('@/modules/landing/modules/products/views/ProductDetailView.vue')
      },
      {
        path: 'categories/:id',
        name: 'category-products',
        component: () => import('@/modules/landing/modules/categories/views/CategoryProductsView.vue')
      },
      {
        path: 'cart',
        name: 'cart',
        component: () => import('@/modules/landing/modules/cart/views/CartView.vue')
      },
      {
        path: 'checkout',
        name: 'checkout',
        component: () => import('@/modules/landing/modules/orders/views/CheckoutView.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'orders',
        name: 'orders',
        component: () => import('@/modules/landing/modules/orders/views/OrderListView.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'orders/:id',
        name: 'order-detail',
        component: () => import('@/modules/landing/modules/orders/views/OrderDetailView.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'wishlist',
        name: 'wishlist',
        component: () => import('@/modules/landing/modules/wishlist/views/WishlistView.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'articles',
        name: 'articles',
        component: () => import('@/modules/landing/modules/articles/views/ArticleListView.vue')
      },
      {
        path: 'articles/:id',
        name: 'article-detail',
        component: () => import('@/modules/landing/modules/articles/views/ArticleDetailView.vue')
      },
      {
        path: 'promotions',
        name: 'promotions',
        component: () => import('@/modules/landing/modules/promotions/views/PromotionListView.vue')
      },
      {
        path: 'promotions/:id',
        name: 'promotion-detail',
        component: () => import('@/modules/landing/modules/promotions/views/PromotionDetailView.vue')
      },
      // Profile with nested routes
      {
        path: 'profile',
        component: () => import('@/modules/landing/modules/profile/ProfileLayout.vue'),
        meta: { requiresAuth: true },
        children: [
          {
            path: '',
            redirect: '/profile/cart'
          },
          {
            path: 'cart',
            name: 'profile-cart',
            component: () => import('@/modules/landing/modules/cart/views/CartView.vue')
          },
          {
            path: 'info',
            name: 'profile-info',
            component: () => import('@/modules/landing/modules/profile/views/ProfileInfoView.vue')
          },
          {
            path: 'address',
            name: 'profile-address',
            component: () => import('@/modules/landing/modules/profile/views/ShippingAddressView.vue')
          },
          {
            path: 'payment',
            name: 'profile-payment',
            component: () => import('@/modules/landing/modules/profile/views/PaymentMethodsView.vue')
          }
        ]
      },
      {
        path: 'orders/:id/success',
        name: 'order-success',
        component: () => import('@/modules/landing/modules/orders/views/OrderSuccessView.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'loyalty',
        name: 'loyalty',
        component: () => import('@/modules/landing/modules/loyalty/views/LoyaltyView.vue'),
        meta: { requiresAuth: true }
      },
      // 404 for landing
      {
        path: ':pathMatch(.*)*',
        name: 'not-found',
        component: () => import('@/shared/components/NotFound.vue')
      }
    ]
  },
  
  // Admin routes
  {
    path: '/admin',
    component: () => import('@/layouts/AdminLayout.vue'),
    meta: { requiresAuth: true, requiresAdmin: true },
    children: [
      {
        path: '',
        name: 'admin-dashboard',
        component: () => import('@/modules/admin/dashboard/views/DashboardView.vue')
      },
      {
        path: 'products',
        name: 'admin-products',
        component: () => import('@/modules/admin/products/views/ProductListView.vue')
      },
      {
        path: 'products/create',
        name: 'admin-products-create',
        component: () => import('@/modules/admin/products/views/ProductFormView.vue')
      },
      {
        path: 'products/:id/edit',
        name: 'admin-products-edit',
        component: () => import('@/modules/admin/products/views/ProductFormView.vue')
      },
      {
        path: 'users',
        name: 'admin-users',
        component: () => import('@/modules/admin/users/views/UserListView.vue')
      },
      {
        path: 'orders',
        name: 'admin-orders',
        component: () => import('@/modules/admin/orders/views/OrderListView.vue')
      },
      {
        path: 'categories',
        name: 'admin-categories',
        component: () => import('@/modules/admin/categories/views/CategoryListView.vue')
      },
      {
        path: 'promotions',
        name: 'admin-promotions',
        component: () => import('@/modules/admin/promotions/views/PromotionListView.vue')
      },
      {
        path: 'warehouse',
        name: 'admin-warehouse',
        redirect: '/admin/warehouse/list'
      },
      {
        path: 'warehouse/list',
        name: 'admin-warehouse-list',
        component: () => import('@/modules/admin/warehouses/views/WarehouseListView.vue')
      },
      {
        path: 'warehouse/inventory',
        name: 'admin-warehouse-inventory',
        component: () => import('@/modules/admin/warehouses/views/InventoryView.vue')
      },
      {
        path: 'warehouse/inbound-receipts',
        name: 'admin-warehouse-inbound-receipts',
        component: () => import('@/modules/admin/warehouses/views/InboundReceiptView.vue')
      },
      {
        path: 'warehouse/inbound-batches',
        name: 'admin-warehouse-inbound-batches',
        component: () => import('@/modules/admin/warehouses/views/InboundBatchView.vue')
      },
      {
        path: 'warehouse/outbound-receipts',
        name: 'admin-warehouse-outbound-receipts',
        component: () => import('@/modules/admin/warehouses/views/OutboundReceiptView.vue')
      },
      {
        path: 'warehouse/adjustments',
        name: 'admin-warehouse-adjustments',
        component: () => import('@/modules/admin/warehouses/views/StockAdjustmentView.vue')
      },
      {
        path: 'warehouse/inventory-logs',
        name: 'admin-warehouse-inventory-logs',
        component: () => import('@/modules/admin/warehouses/views/InventoryLogsView.vue')
      },
      {
        path: 'warehouse/suppliers',
        name: 'admin-warehouse-suppliers',
        component: () => import('@/modules/admin/warehouses/views/SuppliersView.vue')
      },
      {
        path: 'articles',
        name: 'admin-articles',
        component: () => import('@/modules/admin/views/ArticleListView.vue')
      },
      {
        path: 'reviews',
        name: 'admin-reviews',
        component: () => import('@/modules/admin/reviews/views/ReviewListView.vue')
      },
      
      // === NEW ERP ROUTES ===
      
      // Sales - Returns/RMA
      {
        path: 'returns',
        name: 'admin-returns',
        component: () => import('@/modules/admin/erp/views/ReturnsView.vue')
      },
      // Sales - Customers
      {
        path: 'customers',
        name: 'admin-customers',
        component: () => import('@/modules/admin/erp/views/CustomersView.vue')
      },
      
      // Warehouse - Batches
      {
        path: 'warehouse/batches',
        name: 'admin-warehouse-batches',
        component: () => import('@/modules/admin/erp/views/BatchesView.vue')
      },
      // Warehouse - Stocktakes
      {
        path: 'warehouse/stocktakes',
        name: 'admin-warehouse-stocktakes',
        component: () => import('@/modules/admin/erp/views/StocktakesView.vue')
      },
      // Warehouse - Transfers
      {
        path: 'warehouse/transfers',
        name: 'admin-warehouse-transfers',
        component: () => import('@/modules/admin/erp/views/TransfersView.vue')
      },
      // Warehouse - Inventory Alerts
      {
        path: 'warehouse/alerts',
        name: 'admin-warehouse-alerts',
        component: () => import('@/modules/admin/erp/views/InventoryAlertsView.vue')
      },
      
      // Purchase - Requests
      {
        path: 'purchase/requests',
        name: 'admin-purchase-requests',
        component: () => import('@/modules/admin/erp/views/PurchaseRequestsView.vue')
      },
      
      // Finance - Expenses
      {
        path: 'finance/expenses',
        name: 'admin-finance-expenses',
        component: () => import('@/modules/admin/erp/views/ExpensesView.vue')
      },
      // Finance - COD Reconciliation
      {
        path: 'finance/cod-reconciliation',
        name: 'admin-finance-cod',
        component: () => import('@/modules/admin/erp/views/CODReconciliationView.vue')
      },
      
      // Reports
      {
        path: 'reports/sales',
        name: 'admin-reports-sales',
        component: () => import('@/modules/admin/erp/views/SalesReportView.vue')
      },
      {
        path: 'reports/inventory',
        name: 'admin-reports-inventory',
        component: () => import('@/modules/admin/erp/views/InventoryReportView.vue')
      },
      {
        path: 'reports/pnl',
        name: 'admin-reports-pnl',
        component: () => import('@/modules/admin/erp/views/PnLReportView.vue')
      },
      
      // Marketing - Membership
      {
        path: 'marketing/membership',
        name: 'admin-marketing-membership',
        component: () => import('@/modules/admin/erp/views/MembershipView.vue')
      },
      // Marketing - Points
      {
        path: 'marketing/points',
        name: 'admin-marketing-points',
        component: () => import('@/modules/admin/erp/views/PointsView.vue')
      },
      // Marketing - Automations
      {
        path: 'marketing/automations',
        name: 'admin-marketing-automations',
        component: () => import('@/modules/admin/erp/views/AutomationsView.vue')
      },
      
      // Settings - Permissions
      {
        path: 'settings/permissions',
        name: 'admin-settings-permissions',
        component: () => import('@/modules/admin/erp/views/PermissionsView.vue')
      },
      // Settings - Audit Logs
      {
        path: 'settings/audit-logs',
        name: 'admin-settings-audit-logs',
        component: () => import('@/modules/admin/erp/views/AuditLogsView.vue')
      },
      
      // 404 for admin
      {
        path: ':pathMatch(.*)*',
        redirect: '/admin'
      }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) return savedPosition
    return { top: 0 }
  }
})

// Navigation guards
router.beforeEach(async (to, _from, next) => {
  const { useAuthStore } = await import('@/stores')
  const authStore = useAuthStore()

  // Fetch user if not loaded
  if (!authStore.user && !authStore.isLoading) {
    await authStore.fetchUser()
  }

  // Check auth requirements
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return next({ name: 'login', query: { redirect: to.fullPath } })
  }

  // Check admin requirements
  if (to.meta.requiresAdmin && !authStore.isAdmin) {
    return next({ name: 'home' })
  }

  // Redirect authenticated users from guest pages
  if (to.meta.guest && authStore.isAuthenticated) {
    return next({ name: 'home' })
  }

  next()
})

export default router
