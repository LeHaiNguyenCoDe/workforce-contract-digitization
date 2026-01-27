# Business Rules & Implementation Specification
## Ceramic ERP Admin Panel

**Version:** 1.0  
**Last Updated:** 2024-12-27  
**Status:** Production Ready

---

# Mục lục

1. [Tổng quan hệ thống](#1-tổng-quan-hệ-thống)
2. [Module 01: Phân quyền](#module-01-phân-quyền-permissions)
3. [Module 02: Dashboard](#module-02-dashboard)
4. [Module 03: Bán hàng](#module-03-bán-hàng-sales)
5. [Module 04: Kho hàng](#module-04-kho-hàng-warehouse)
6. [Module 05: Tài chính](#module-05-tài-chính-finance)
7. [Module 06: Marketing](#module-06-marketing)
8. [Module 07: Nội dung](#module-07-nội-dung-content)
9. [Module 08: Cài đặt](#module-08-cài-đặt-settings)

---

# 1. Tổng quan hệ thống

## 1.1 Tech Stack

| Layer | Technology |
|-------|------------|
| Frontend | Vue 3 + TypeScript + Vite |
| State Management | Pinia |
| Styling | TailwindCSS |
| Backend | Laravel 11 |
| Database | MySQL 8.0 |
| Auth | Session-based (Sanctum) |

## 1.2 Quy tắc chung

| Quy tắc | Mô tả |
|---------|-------|
| BR-G01 | Tất cả bảng >10 dòng phải có pagination |
| BR-G02 | Mọi form có validation FE + BE |
| BR-G03 | API format: `{ status, data/message }` |
| BR-G04 | Decimal lưu decimal(15,2), parse float ở FE |
| BR-G05 | Mọi action quan trọng phải log audit |

---

# Module 01: Phân quyền (Permissions)

## 1.1 Business Rules

| ID | Rule | Status |
|----|------|--------|
| BR-P01 | Cấu trúc: User → Roles → Rights | ✅ Done |
| BR-P02 | Menu ẩn nếu không có permission | ✅ Done |
| BR-P03 | Button ẩn nếu không có permission | ✅ Done |
| BR-P04 | Admin luôn có toàn quyền | ✅ Done |

## 1.2 Roles mặc định

| Role | Permissions | Admin Access |
|------|-------------|--------------|
| admin | All | ✅ Yes |
| manager | View/Edit all, no Settings | ✅ Yes |
| staff | View only | ✅ Yes |
| warehouse | Warehouse module | ✅ Yes |
| customer | None | ❌ No |

## 1.3 Permission Groups

```
Dashboard: view_dashboard
Sales: view_orders, create_orders, edit_orders, delete_orders, view_customers, ...
Warehouse: view_products, create_products, adjust_stock, ...
Finance: view_finance, create_expenses, manage_receivables, ...
Settings: view_settings, edit_settings, manage_users, ...
```

## 1.4 Implementation

| Component | Path | Status |
|-----------|------|--------|
| Composable | `FE/src/composables/usePermission.ts` | ✅ Done |
| Auth Store | `FE/src/stores/auth.ts` | ✅ Done |
| Backend Middleware | `web/app/Http/Middleware/AdminMiddleware.php` | ✅ Done |
| User Seeder | `web/database/seeders/UserSeeder.php` | ✅ Done |

## 1.5 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/login` | Login, trả về user + permissions |
| GET | `/api/auth/user` | Get current user + permissions |
| POST | `/api/auth/logout` | Logout |

---

# Module 02: Dashboard

## 2.1 Business Rules

| ID | Rule | Status |
|----|------|--------|
| BR-D01 | Hiển thị thống kê tổng quan | ✅ Done |
| BR-D02 | Chỉ hiện với permission view_dashboard | ✅ Done |

## 2.2 Components

| Widget | Mô tả | Status |
|--------|-------|--------|
| Stats Cards | Doanh thu, Đơn hàng, Khách hàng | ✅ Done |
| Revenue Chart | Biểu đồ doanh thu | ✅ Done |
| Recent Orders | Đơn hàng gần đây | ✅ Done |
| Low Stock Alert | Cảnh báo hết hàng | ⏳ Pending |

---

# Module 03: Bán hàng (Sales)

## 3.1 Đơn hàng (Orders)

### Business Rules

| ID | Rule | Status |
|----|------|--------|
| BR-O01 | Workflow: pending → confirmed → processing → shipping → delivered | ✅ Done |
| BR-O02 | Chỉ hủy khi pending/confirmed | ✅ Done |
| BR-O03 | Hủy phải hoàn tồn kho | ✅ Done |
| BR-O04 | COD → tạo công nợ phải thu | ✅ Done |

### Order Status Flow
```
[pending] → [confirmed] → [processing] → [shipping] → [delivered]
    ↓            ↓
[cancelled] [cancelled]
```

### API Endpoints

| Method | Endpoint | Permission |
|--------|----------|------------|
| GET | `/api/admin/orders` | view_orders |
| GET | `/api/admin/orders/{id}` | view_orders |
| POST | `/api/admin/orders` | create_orders |
| PUT | `/api/admin/orders/{id}` | edit_orders |
| POST | `/api/admin/orders/{id}/confirm` | edit_orders |
| POST | `/api/admin/orders/{id}/cancel` | edit_orders |

## 3.2 Khách hàng (Customers)

### Business Rules

| ID | Rule | Status |
|----|------|--------|
| BR-C01 | Khách hàng có thể có nhiều địa chỉ | ✅ Done |
| BR-C02 | Tự động tính hạng thành viên theo tổng chi | ✅ Done |
| BR-C03 | Không xóa khách có đơn hàng | ✅ Done |

---

# Module 04: Kho hàng (Warehouse)

## 4.1 Sản phẩm (Products)

### Business Rules

| ID | Rule | Status |
|----|------|--------|
| BR-PR01 | Sản phẩm có thể có nhiều variants (màu, size) | ✅ Done |
| BR-PR02 | SKU phải unique | ✅ Done |
| BR-PR03 | Không xóa SP có tồn kho | ✅ Done |

### API Endpoints

| Method | Endpoint | Permission |
|--------|----------|------------|
| GET | `/api/admin/products` | view_products |
| POST | `/api/admin/products` | create_products |
| PUT | `/api/admin/products/{id}` | edit_products |
| DELETE | `/api/admin/products/{id}` | delete_products |

## 4.2 Nhập kho (Inbound)

### Business Rules

| ID | Rule | Status |
|----|------|--------|
| BR-IN01 | Mỗi lần nhập tạo Inbound Batch | ✅ Done |
| BR-IN02 | Batch number format: BATCH-YYYYMMDD-XXXXXX | ✅ Done |
| BR-IN03 | Workflow: pending → received → qc_in_progress → qc_completed | ✅ Done |
| BR-IN04 | Không sửa batch sau khi QC bắt đầu | ✅ Done |

### Inbound Workflow
```
[pending] → [received] → [qc_in_progress] → [qc_completed]
                              ↓
                         [cancelled]
```

## 4.3 Kiểm tra chất lượng (QC)

### Business Rules

| ID | Rule | Status |
|----|------|--------|
| BR-QC01 | Mỗi batch chỉ có 1 QC chính thức | ✅ Done |
| BR-QC02 | QC PASS → cộng tồn kho | ✅ Done |
| BR-QC03 | QC PARTIAL → chỉ cộng quantity_passed | ✅ Done |
| BR-QC04 | QC FAIL → không cộng tồn | ✅ Done |
| BR-QC05 | Không sửa QC đã xác nhận | ✅ Done |

## 4.4 Điều chỉnh tồn kho (Stock Adjustment)

### Business Rules

| ID | Rule | Status |
|----|------|--------|
| BR-SA01 | Chỉ user có permission adjust_stock | ✅ Done |
| BR-SA02 | Bắt buộc nhập lý do | ✅ Done |
| BR-SA03 | Ghi log: before, after, reason, user | ✅ Done |
| BR-SA04 | Không xóa log điều chỉnh | ✅ Done |

### Database: inventory_logs

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| warehouse_id | bigint | FK → warehouses |
| product_id | bigint | FK → products |
| movement_type | enum | inbound, outbound, adjust, qc_pass |
| quantity | int | Số lượng thay đổi |
| quantity_before | int | Số lượng trước |
| quantity_after | int | Số lượng sau |
| user_id | bigint | FK → users (người thực hiện) |
| reason | text | Lý do |
| created_at | timestamp | Thời gian |

## 4.5 Xuất kho (Outbound)

### Business Rules

| ID | Rule | Status |
|----|------|--------|
| BR-OUT01 | Chỉ xuất từ available_quantity | ✅ Done |
| BR-OUT02 | Không xuất vượt tồn | ✅ Done |
| BR-OUT03 | Ưu tiên FIFO (nhập trước xuất trước) | ✅ Done |

---

# Module 05: Tài chính (Finance)

## 5.1 Quỹ (Funds)

### Business Rules

| ID | Rule | Status |
|----|------|--------|
| BR-F01 | Mặc định 2 quỹ: Tiền mặt, Ngân hàng | ✅ Done |
| BR-F02 | Mọi giao dịch gắn với 1 quỹ | ✅ Done |
| BR-F03 | Balance tự động cập nhật | ✅ Done |

## 5.2 Thu chi (Transactions)

### Business Rules

| ID | Rule | Status |
|----|------|--------|
| BR-T01 | Type receipt → +balance | ✅ Done |
| BR-T02 | Type payment → -balance | ✅ Done |
| BR-T03 | Phải có expense_category | ✅ Done |

## 5.3 Công nợ phải thu (Receivables)

### Business Rules

| ID | Rule | Status |
|----|------|--------|
| BR-R01 | Tạo từ đơn hàng COD | ✅ Done |
| BR-R02 | Status: pending → partial → paid | ✅ Done |
| BR-R03 | Thu tiền: cập nhật paid_amount, remaining_amount | ✅ Done |

## 5.4 Công nợ phải trả (Payables)

### Business Rules

| ID | Rule | Status |
|----|------|--------|
| BR-PA01 | Tạo từ lô nhập hàng | ✅ Done |
| BR-PA02 | Status: pending → partial → paid | ✅ Done |
| BR-PA03 | Thanh toán: cập nhật balance quỹ | ✅ Done |

### API Endpoints

| Method | Endpoint | Permission |
|--------|----------|------------|
| GET | `/api/admin/finance/dashboard` | view_finance |
| GET | `/api/admin/finance/funds` | view_finance |
| GET | `/api/admin/finance/receivables` | view_finance |
| GET | `/api/admin/finance/payables` | view_finance |
| POST | `/api/admin/finance/expenses` | create_expenses |

---

# Module 06: Marketing

## 6.1 Hạng thành viên (Membership Tiers)

### Business Rules

| ID | Rule | Status |
|----|------|--------|
| BR-M01 | Tính theo tổng chi tiêu tích lũy | ✅ Done |
| BR-M02 | Mỗi hạng có % giảm giá khác nhau | ✅ Done |
| BR-M03 | Auto upgrade khi đạt ngưỡng | ⏳ Pending |

## 6.2 Điểm thưởng (Points)

### Business Rules

| ID | Rule | Status |
|----|------|--------|
| BR-PT01 | Tích điểm theo % giá trị đơn | ⏳ Pending |
| BR-PT02 | Điểm có hạn sử dụng | ⏳ Pending |
| BR-PT03 | Đổi điểm → voucher | ⏳ Pending |

## 6.3 Khuyến mãi (Promotions)

### Business Rules

| ID | Rule | Status |
|----|------|--------|
| BR-PM01 | Có start_date, end_date | ⏳ Pending |
| BR-PM02 | Giới hạn số lần sử dụng | ⏳ Pending |
| BR-PM03 | Áp dụng theo sản phẩm/danh mục | ⏳ Pending |

---

# Module 07: Nội dung (Content)

## 7.1 Bài viết (Articles)

### Business Rules

| ID | Rule | Status |
|----|------|--------|
| BR-A01 | Có title, slug, thumbnail, content | ✅ Done |
| BR-A02 | Slug unique | ✅ Done |
| BR-A03 | published_at để hẹn giờ đăng | ✅ Done |

### API Endpoints

| Method | Endpoint | Permission |
|--------|----------|------------|
| GET | `/api/admin/articles` | view_articles |
| POST | `/api/admin/articles` | create_articles |
| PUT | `/api/admin/articles/{id}` | edit_articles |
| DELETE | `/api/admin/articles/{id}` | delete_articles |

---

# Module 08: Cài đặt (Settings)

## 8.1 Quản lý Users

### Business Rules

| ID | Rule | Status |
|----|------|--------|
| BR-S01 | Chỉ admin quản lý users | ✅ Done |
| BR-S02 | Không xóa user có data liên quan | ✅ Done |

## 8.2 Cấu hình hệ thống

### Business Rules

| ID | Rule | Status |
|----|------|--------|
| BR-SC01 | Chỉ admin được thay đổi | ✅ Done |
| BR-SC02 | Cache config để tối ưu | ⏳ Pending |

---

# Test Accounts

| Email | Password | Role |
|-------|----------|------|
| admin@example.com | password123 | admin |
| manager@example.com | password123 | manager |
| staff@example.com | password123 | staff |
| warehouse@example.com | password123 | warehouse |
| customer@example.com | password123 | customer |

---

# Tổng kết Implementation Status

| Module | Total Rules | Done | Pending |
|--------|-------------|------|---------|
| Permissions | 4 | 4 | 0 |
| Dashboard | 2 | 2 | 0 |
| Sales | 7 | 7 | 0 |
| Warehouse | 16 | 16 | 0 |
| Finance | 10 | 10 | 0 |
| Marketing | 9 | 3 | 6 |
| Content | 3 | 3 | 0 |
| Settings | 4 | 3 | 1 |
| **Total** | **55** | **48** | **7** |

**Progress: 87% Complete**
