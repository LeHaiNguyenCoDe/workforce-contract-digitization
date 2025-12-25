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
        component: () => import('@/modules/admin/warehouses/views/WarehouseDashboard.vue')
      },
      {
        path: 'warehouse/products',
        name: 'admin-warehouse-products',
        component: () => import('@/modules/admin/warehouses/views/WarehouseProductsView.vue')
      },
      {
        path: 'warehouse/suppliers',
        name: 'admin-warehouse-suppliers',
        component: () => import('@/modules/admin/warehouses/views/SuppliersView.vue')
      },
      {
        path: 'warehouse/quality',
        name: 'admin-warehouse-quality',
        component: () => import('@/modules/admin/warehouses/views/QualityCheckView.vue')
      },
      {
        path: 'warehouse/list',
        name: 'admin-warehouse-list',
        component: () => import('@/modules/admin/warehouses/views/WarehouseListView.vue')
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
