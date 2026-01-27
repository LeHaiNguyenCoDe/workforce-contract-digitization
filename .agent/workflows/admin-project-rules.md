---
description: Quy tắc và phân tích dự án Admin Panel - Ceramic ERP
---

# Tổng quan dự án Admin

## Tech Stack

### Frontend (FE/)
- **Framework**: Vue 3 + Vite + TypeScript
- **State Management**: Pinia stores
- **Styling**: TailwindCSS + Custom design system
- **Router**: Vue Router với route guards
- **Auto-imports**: unplugin-auto-import cho Vue/Pinia

### Backend (web/)
- **Framework**: Laravel 11
- **Database**: MySQL với Eloquent ORM
- **Auth**: Session-based (Sanctum)
- **API**: RESTful JSON responses

---

## Cấu trúc thư mục chính

```
FE/src/
├── composables/          # Shared logic (usePermission, useSwal...)
├── layouts/              # AdminLayout, StoreLayout
├── modules/admin/        # Admin modules
│   ├── erp/              # ERP modules (finance, permissions...)
│   └── warehouses/       # Warehouse management
├── plugins/api/          # HTTP client, services, types
├── shared/components/    # Shared UI components
└── stores/               # Pinia stores (auth.ts)

web/
├── app/
│   ├── Http/Controllers/ # Controllers
│   ├── Models/           # Eloquent models
│   ├── Services/         # Business logic
│   └── Repositories/     # Data access layer
├── database/seeders/     # Data seeders
└── routes/api.php        # API routes
```

---

## Quy tắc phân quyền (Permissions)

### Cấu trúc Backend
```
User → Roles → Rights
```
- **User** có nhiều **Roles** (admin, manager, staff, warehouse, customer)
- **Role** có nhiều **Rights** (view_products, create_orders, ...)
- Backend trả về `user.permissions[]` trong login response

### Frontend Implementation
```typescript
// composables/usePermission.ts
const { hasPermission, hasAnyPermission, isAdmin } = usePermission()

// Ẩn menu theo quyền (AdminLayout.vue)
<button v-if="hasPermission('adjust_stock')">Tạo điều chỉnh</button>

// Permission names format: action_module
// Examples: view_products, create_orders, edit_customers, delete_articles
```

### Danh sách Roles có sẵn
| Role | Description | Access Level |
|------|-------------|--------------|
| admin | Administrator | Toàn quyền |
| manager | Quản lý | Vận hành (không có settings) |
| staff | Nhân viên | Chỉ xem |
| warehouse | NV Kho | Quản lý kho |
| customer | Khách hàng | Không vào admin |

---

## Quy tắc code Frontend

### 1. Component Structure
```vue
<script setup lang="ts">
// 1. Imports
// 2. Store & Composables
// 3. State (ref, reactive)
// 4. Computed
// 5. Methods
// 6. Lifecycle hooks
</script>

<template>
  <!-- Template -->
</template>
```

### 2. Pagination Pattern
```typescript
const currentPage = ref(1)
const itemsPerPage = ref(10)
const totalPages = computed(() => Math.ceil(data.length / itemsPerPage.value))
const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  return data.slice(start, start + itemsPerPage.value)
})
watch(searchQuery, () => { currentPage.value = 1 })
```

### 3. API Service Pattern
```typescript
// plugins/api/services/ExampleService.ts
export const exampleService = {
  getAll: () => httpClient.get('/admin/examples'),
  create: (data) => httpClient.post('/admin/examples', data),
  update: (id, data) => httpClient.put(`/admin/examples/${id}`, data),
  delete: (id) => httpClient.delete(`/admin/examples/${id}`),
}
```

---

## Quy tắc code Backend

### 1. Controller Response Format
```php
return response()->json([
    'status' => 'success',
    'data' => $data,
    'message' => 'Optional message'
]);

// Error response
return response()->json([
    'status' => 'error',
    'message' => 'Error message'
], 400);
```

### 2. Seeder với User ID
```php
// Luôn set user_id cho audit logs
$adminUser = User::where('email', 'admin@example.com')->first();
$userId = $adminUser?->id ?? 1;

InventoryLog::create([
    'user_id' => $userId,
    // ...other fields
]);
```

### 3. Relations để load
```php
// Load relations with eager loading
->with(['warehouse', 'product', 'user']);
```

---

## Menu Items với Permissions

```typescript
// AdminLayout.vue
{ icon: 'dashboard', path: '/admin', label: 'Dashboard', requiredPermission: 'view_dashboard' },
{ icon: 'orders', path: '/admin/orders', label: 'Đơn hàng', requiredPermission: 'view_orders' },
{ icon: 'products', path: '/admin/products', label: 'Sản phẩm', requiredPermission: 'view_products' },
{ icon: 'finance', path: '/admin/finance', label: 'Tài chính', requiredPermission: 'view_finance' },
{ icon: 'settings', path: '/admin/settings', label: 'Cấu hình', requiredPermission: 'view_settings' },
```

---

## Test Users

| Email | Password | Role |
|-------|----------|------|
| admin@example.com | password123 | admin |
| manager@example.com | password123 | manager |
| staff@example.com | password123 | staff |
| warehouse@example.com | password123 | warehouse |
| customer@example.com | password123 | customer |

---

## Common Issues & Solutions

### Vetur/TypeScript False Positives
- "Cannot find module" errors for auto-imported stores/composables are false positives
- App uses unplugin-auto-import - no explicit imports needed for Vue/Pinia

### API Decimal Values
- Backend decimal columns return as strings
- Frontend must parseFloat() before calculations
```typescript
const totalFundBalance = computed(() => 
  funds.value.reduce((sum, f) => sum + parseFloat(f.balance as any || 0), 0)
)
```

### Session Auth với CORS
- Frontend proxy qua Vite dev server để tránh CORS
- Backend sử dụng session cookies, không token
