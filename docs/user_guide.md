# Hướng dẫn Sử dụng Admin Panel
## Hệ thống Quản lý Gốm sứ (Ceramic ERP)

**Version:** 1.0  
**Cập nhật:** 27/12/2024

---

# Mục lục

1. [Đăng nhập & Phân quyền](#1-đăng-nhập--phân-quyền)
2. [Dashboard](#2-dashboard)
3. [Module Bán hàng](#3-module-bán-hàng)
4. [Module Kho hàng](#4-module-kho-hàng)
5. [Module Tài chính](#5-module-tài-chính)
6. [Module Marketing](#6-module-marketing)
7. [Module Nội dung](#7-module-nội-dung)
8. [Cài đặt hệ thống](#8-cài-đặt-hệ-thống)

---

# 1. Đăng nhập & Phân quyền

## 1.1 Đăng nhập hệ thống

1. Truy cập: `http://localhost:5173/admin/login`
2. Nhập **Email** và **Mật khẩu**
3. Nhấn **Đăng nhập**

### Tài khoản mặc định

| Email | Mật khẩu | Vai trò |
|-------|----------|---------|
| admin@example.com | password123 | Quản trị viên |
| manager@example.com | password123 | Quản lý |
| staff@example.com | password123 | Nhân viên |
| warehouse@example.com | password123 | NV Kho |

## 1.2 Phân quyền theo vai trò

| Vai trò | Mô tả | Menu hiển thị |
|---------|-------|---------------|
| **Admin** | Toàn quyền | Tất cả |
| **Manager** | Quản lý vận hành | Tất cả trừ Cài đặt |
| **Staff** | Chỉ xem | Dashboard, Đơn hàng, Sản phẩm |
| **Warehouse** | Quản lý kho | Kho hàng |

> **Lưu ý:** Các nút tạo/sửa/xóa sẽ tự động ẩn nếu bạn không có quyền tương ứng.

---

# 2. Dashboard

**Đường dẫn:** `Admin → Dashboard`

## 2.1 Các widget hiển thị

| Widget | Mô tả |
|--------|-------|
| **Thống kê tổng quan** | Doanh thu, Số đơn hàng, Khách hàng mới |
| **Biểu đồ doanh thu** | Theo ngày/tuần/tháng |
| **Đơn hàng gần đây** | 10 đơn mới nhất |
| **Sản phẩm sắp hết** | Cảnh báo tồn kho thấp |

---

# 3. Module Bán hàng

## 3.1 Quản lý Đơn hàng

**Đường dẫn:** `Admin → Bán hàng → Đơn hàng`

### Danh sách đơn hàng
- Xem tất cả đơn hàng với bộ lọc theo trạng thái
- Tìm kiếm theo mã đơn, tên khách hàng

### Trạng thái đơn hàng

| Trạng thái | Màu | Mô tả |
|------------|-----|-------|
| Chờ xử lý | 🟡 Vàng | Đơn mới tạo |
| Đã xác nhận | 🔵 Xanh dương | Đã xác nhận với khách |
| Đang xử lý | 🟣 Tím | Đang chuẩn bị hàng |
| Đang giao | 🟠 Cam | Đã giao cho ĐVVC |
| Hoàn thành | 🟢 Xanh lá | Đã giao thành công |
| Đã hủy | 🔴 Đỏ | Đơn bị hủy |

### Thao tác

| Thao tác | Điều kiện | Permission |
|----------|-----------|------------|
| Xem chi tiết | Tất cả | view_orders |
| Xác nhận đơn | Chờ xử lý | edit_orders |
| Hủy đơn | Chờ xử lý/Đã xác nhận | edit_orders |
| Xóa đơn | Đơn đã hủy | delete_orders |

## 3.2 Quản lý Khách hàng

**Đường dẫn:** `Admin → Bán hàng → Khách hàng`

- Xem danh sách khách hàng
- Xem lịch sử đơn hàng của từng khách
- Xem hạng thành viên và điểm tích lũy

---

# 4. Module Kho hàng

## 4.1 Quản lý Sản phẩm

**Đường dẫn:** `Admin → Kho → Sản phẩm`

### Thao tác

| Thao tác | Mô tả | Permission |
|----------|-------|------------|
| Thêm sản phẩm | Tạo sản phẩm mới | create_products |
| Sửa sản phẩm | Cập nhật thông tin | edit_products |
| Xóa sản phẩm | Xóa (không có tồn kho) | delete_products |

### Thông tin sản phẩm

- **Tên sản phẩm**: Tên hiển thị
- **SKU**: Mã sản phẩm (unique)
- **Danh mục**: Gốm trang trí, Gia dụng, Tâm linh...
- **Giá bán**: Giá niêm yết
- **Variants**: Các biến thể (màu men, kích thước)

## 4.2 Danh mục sản phẩm

**Đường dẫn:** `Admin → Kho → Danh mục`

### Cấu trúc danh mục

```
Gốm Trang Trí
├── Bình Gốm
├── Tranh Gốm
└── Tượng Gốm

Gốm Gia Dụng
├── Bộ Ấm Chén
├── Bát Đĩa
└── Hũ Gạo - Chum Rượu

Gốm Tâm Linh
├── Đồ Thờ Cúng
└── Quà Tặng Tâm Linh
```

## 4.3 Lô nhập kho (Inbound)

**Đường dẫn:** `Admin → Kho → Phiếu nhập`

### Quy trình nhập kho

```
1. Tạo phiếu nhập (PENDING)
   ↓
2. Nhận hàng (RECEIVED)
   ↓
3. Kiểm tra chất lượng (QC)
   ↓
4. Cập nhật tồn kho (QC_COMPLETED)
```

### Thao tác

| Bước | Thao tác | Mô tả |
|------|----------|-------|
| 1 | Tạo phiếu | Chọn NCC, kho, danh sách SP |
| 2 | Nhận hàng | Ghi nhận SL thực nhận |
| 3 | Kiểm định | Đánh giá PASS/FAIL/PARTIAL |
| 4 | Duyệt | Tự động cộng tồn kho |

## 4.4 Phiếu xuất kho (Outbound)

**Đường dẫn:** `Admin → Kho → Phiếu xuất`

### Mục đích xuất

| Loại | Mô tả |
|------|-------|
| sales | Xuất bán hàng |
| transfer | Chuyển kho |
| return | Trả NCC |
| damage | Hư hỏng |

## 4.5 Điều chỉnh tồn kho ⚠️

**Đường dẫn:** `Admin → Kho → Điều chỉnh tồn`

> **Cảnh báo:** Đây là nghiệp vụ đặc biệt, bắt buộc phải có lý do!

### Quy trình điều chỉnh

1. Nhấn **Tạo điều chỉnh**
2. Chọn **Kho** và **Sản phẩm**
3. Nhập **Số lượng mới**
4. Nhập **Lý do** (bắt buộc)
5. Nhấn **Xác nhận**

### Thông tin hiển thị

| Cột | Mô tả |
|-----|-------|
| Thời gian | Ngày giờ điều chỉnh |
| Kho | Kho thực hiện |
| Sản phẩm | SP điều chỉnh |
| Trước | Số lượng trước |
| Sau | Số lượng sau |
| Chênh lệch | + tăng / - giảm |
| Lý do | Lý do điều chỉnh |
| Người thực hiện | User thực hiện |

---

# 5. Module Tài chính

## 5.1 Dashboard Tài chính

**Đường dẫn:** `Admin → Tài chính → Tổng quan`

### Thông tin hiển thị

| Widget | Mô tả |
|--------|-------|
| **Tổng quỹ** | Tổng tiền các quỹ |
| **Thu trong kỳ** | Tổng thu |
| **Chi trong kỳ** | Tổng chi |
| **Công nợ** | Phải thu + Phải trả |

## 5.2 Quản lý Quỹ

**Đường dẫn:** `Admin → Tài chính → Quỹ tiền`

### Quỹ mặc định

| Quỹ | Mô tả |
|-----|-------|
| Tiền mặt | Tiền mặt tại cửa hàng |
| Ngân hàng VCB | Tài khoản ngân hàng |

## 5.3 Thu chi

**Đường dẫn:** `Admin → Tài chính → Chi phí`

### Danh mục chi phí

| Mã | Loại | Mô tả |
|----|------|-------|
| RENT | Chi | Thuê mặt bằng |
| ELECTRIC | Chi | Tiền điện |
| SALARY | Chi | Lương nhân viên |
| SHIPPING | Chi | Phí vận chuyển |
| SALES | Thu | Doanh thu bán hàng |
| COD_INCOME | Thu | Thu COD |

## 5.4 Công nợ phải thu

**Đường dẫn:** `Admin → Tài chính → Phải thu`

### Thao tác

| Nút | Mô tả | Permission |
|-----|-------|------------|
| Thu tiền | Ghi nhận thanh toán | manage_receivables |
| Chi tiết | Xem chi tiết công nợ | view_finance |

### Trạng thái

| Status | Mô tả |
|--------|-------|
| pending | Chưa thu |
| partial | Thu một phần |
| paid | Đã thu đủ |

## 5.5 Công nợ phải trả

**Đường dẫn:** `Admin → Tài chính → Phải trả`

### Thao tác

| Nút | Mô tả | Permission |
|-----|-------|------------|
| Thanh toán | Ghi nhận thanh toán NCC | manage_payables |
| Chi tiết | Xem chi tiết công nợ | view_finance |

---

# 6. Module Marketing

## 6.1 Hạng thành viên

**Đường dẫn:** `Admin → Marketing → Hạng thành viên`

### Cấu hình hạng

| Hạng | Ngưỡng chi tiêu | Ưu đãi |
|------|-----------------|--------|
| Bronze | 0đ | 0% |
| Silver | 5.000.000đ | 3% |
| Gold | 15.000.000đ | 5% |
| Platinum | 50.000.000đ | 10% |

## 6.2 Điểm thưởng

**Đường dẫn:** `Admin → Marketing → Điểm thưởng`

- Xem điểm tích lũy của khách hàng
- Cấu hình tỷ lệ tích điểm

## 6.3 Khuyến mãi

**Đường dẫn:** `Admin → Marketing → Khuyến mãi`

- Tạo mã khuyến mãi
- Thiết lập thời gian hiệu lực
- Giới hạn số lần sử dụng

---

# 7. Module Nội dung

## 7.1 Bài viết

**Đường dẫn:** `Admin → Bài viết`

### Thao tác

| Nút | Mô tả | Permission |
|-----|-------|------------|
| Thêm bài | Tạo bài viết mới | create_articles |
| Sửa | Chỉnh sửa nội dung | edit_articles |
| Xóa | Xóa bài viết | delete_articles |

### Thông tin bài viết

- **Tiêu đề**: Tiêu đề bài viết
- **Slug**: URL-friendly (auto generate)
- **Thumbnail**: Ảnh đại diện
- **Nội dung**: Rich text editor
- **Ngày đăng**: Hẹn giờ đăng bài

---

# 8. Cài đặt hệ thống

**Đường dẫn:** `Admin → Cài đặt`

> **Lưu ý:** Chỉ Admin mới có quyền truy cập

## 8.1 Quản lý Users

- Thêm/sửa/xóa người dùng
- Gán vai trò (roles) cho người dùng

## 8.2 Phân quyền

- Xem danh sách vai trò
- Cấu hình quyền cho từng vai trò

## 8.3 Cấu hình chung

- Thông tin cửa hàng
- Cấu hình email
- Cấu hình thanh toán

---

# Tips & Tricks

## Phím tắt

| Phím | Chức năng |
|------|-----------|
| `/` | Focus vào ô tìm kiếm |
| `Esc` | Đóng modal |

## Pagination

- Mặc định hiển thị 10 dòng/trang
- Có thể chọn: 10, 20, 50, 100 dòng

## Lọc & Tìm kiếm

- Hầu hết các bảng có ô tìm kiếm realtime
- Một số bảng có bộ lọc theo trạng thái

---

# Liên hệ hỗ trợ

**Email:** support@ceramicerp.com  
**Hotline:** 1900-xxx-xxx

> **Lưu ý quan trọng về đồ gốm:**  
> Đồ gốm là hàng dễ vỡ. Khi thực hiện **Kiểm kê (Stocktake)**, cần ghi rõ lý do "Hao hụt/Vỡ hỏng" trong phần giải trình để hệ thống cập nhật tồn kho chính xác.
