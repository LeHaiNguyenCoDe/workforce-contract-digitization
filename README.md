# Workforce Contract Digitization

![Project Status](https://img.shields.io/badge/Project_Status-Active_Development-success)
![Version](https://img.shields.io/badge/Version-2.0.0-blue)
![License](https://img.shields.io/badge/License-Private-red)

## 🌟 Giới thiệu tổng quan

**Workforce Contract Digitization** là nền tảng chuyển đổi số toàn diện, kết hợp sức mạnh của **Thương mại điện tử (E-Commerce)** và **Quản trị doanh nghiệp (ERP)**. Hệ thống được xây dựng trên kiến trúc **Decoupled Monolith**, tách biệt hoàn toàn giữa Frontend và Backend, đảm bảo khả năng mở rộng linh hoạt, hiệu năng cao và trải nghiệm người dùng tối ưu.

Dự án không chỉ dừng lại ở việc bán hàng, mà còn số hóa quy trình vận hành nội bộ, từ quản lý kho, nhân sự, tài chính đến chăm sóc khách hàng tự động.

## 🚀 Tính năng cốt lõi

### 🛒 E-Commerce & Marketplace
*   **Trải nghiệm mua sắm hiện đại**: Tìm kiếm thông minh, bộ lọc sản phẩm đa tầng, gợi ý sản phẩm.
*   **Quy trình thanh toán (Checkout)**: Tối ưu hóa các bước, hỗ trợ đa dạng cổng thanh toán (VNPAY, VietQR), tính phí vận chuyển tự động.
*   **Chương trình khuyến mãi**: Quản lý Voucher, Flash Sale, Giảm giá theo cấp bậc thành viên.

### 🏢 Quản trị Doanh nghiệp (ERP)
*   **Quản lý Kho (WMS)**: Theo dõi tồn kho Real-time, quản lý phiếu nhập/xuất, cảnh báo mức tồn kho an toàn.
*   **Quản lý Đơn hàng (OMS)**: Quy trình xử lý đơn hàng khép kín (Đặt hàng -> Xác nhận -> Đóng gói -> Giao vận -> Đối soát).
*   **Quản trị Quan hệ Khách hàng (CRM)**: Hồ sơ khách hàng 360 độ, lịch sử mua hàng, phân nhóm khách hàng, tích điểm Loyalty.
*   **Tài chính & Kế toán**: Theo dõi doanh thu, công nợ, quản lý dòng tiền thu chi.

### ⚡ Công nghệ Real-time & Tương tác
*   **Hệ thống Chat thông minh**: Guest Chat hỗ trợ khách hàng ngay lập tức qua WebSocket (Laravel Reverb).
*   **Hệ thống Thông báo (Notifications)**: Cập nhật trạng thái đơn hàng, tin nhắn và sự kiện quan trọng tức thì.

## 🛠️ Tech Stack & Kiến trúc

### Backend (`/web`)
Đóng vai trò là **Logic Engine** mạnh mẽ, xử lý nghiệp vụ phức tạp và bảo mật dữ liệu.
*   **Core Framework**: Laravel 11.x
*   **Language**: PHP 8.2+
*   **Database**: MySQL 8.0
*   **Real-time Server**: Laravel Reverb (High-performance WebSocket)
*   **Queue System**: Redis (Xử lý tác vụ nền hiệu suất cao)
*   **API Standard**: RESTful API / OpenAPI 3.0 Specification

### Frontend (`/FE`)
Giao diện người dùng mượt mà (SPA), tốc độ phản hồi cực nhanh.
*   **Framework**: Vue 3 (Composition API)
*   **Language**: TypeScript (Strongly typed)
*   **Build Tool**: Vite 5 (Fast HMR & Optimized Build)
*   **State Management**: Pinia
*   **UI System**: SCSS, Bootstrap Vue Next

## 📂 Truy cập nhanh mã nguồn

Hệ thống được tổ chức khoa học thành các module chuyên biệt. Dưới đây là sơ đồ cấu trúc chi tiết:

<details open>
<summary><b>1. Frontend Structure (`FE/src`)</b></summary>

```
FE/src/
├── modules/
│   ├── admin/               # — Phân hệ Quản trị (Admin Portal)
│   │   ├── dashboard/       # Báo cáo & Thống kê tổng quan
│   │   ├── erp/             # Các tính năng quản trị chuyên sâu (Finance, CRM, HRM)
│   │   ├── orders/          # Trung tâm xử lý đơn hàng & Vận đơn
│   │   ├── products/        # Quản lý danh mục, sản phẩm & kho
│   │   └── chat/            # Hệ thống Chat Support cho Admin
│   ├── marketplace/         # — Phân hệ Mua sắm (Storefront)
│   │   ├── shop/            # Trang danh sách & chi tiết sản phẩm
│   │   ├── cart/            # Quản lý Giỏ hàng & Mini-cart
│   │   └── checkout/        # Quy trình thanh toán & Đặt hàng
│   └── landing/             # — Trang chủ & Thông tin chung
├── stores/                  # Pinia Global State (Auth, Cart, Toast)
├── router/                  # Cấu hình điều hướng ứng dụng
└── components/              # Thư viện UI Components dùng chung
```
</details>

<details>
<summary><b>2. Backend Structure (`web/app`)</b></summary>

```
web/app/
├── Http/Controllers/
│   ├── Api/Modules/         # API Controllers phân theo chức năng
│   │   ├── Admin/           # API Endpoints cho Admin Portal
│   │   └── Landing/         # API Endpoints cho Customer Portal
├── Services/                # — Business Logic Layer (Lớp xử lý nghiệp vụ)
│   ├── Admin/               # Logic quản trị (FinanceCalc, StockOp, Report...)
│   ├── Core/                # Các dịch vụ nền tảng (FileUpload, Logger, Notification)
│   └── Marketing/           # Logic tính toán khuyến mãi & chiến dịch
└── Models/                  # Eloquent Entities (Định nghĩa cấu trúc dữ liệu)
```
</details>

## ⚙️ Hướng dẫn Cài đặt & Triển khai

Để thiết lập môi trường phát triển (Local Development), vui lòng thực hiện theo các bước sau:

### Yêu cầu tiên quyết
*   **PHP**: >= 8.2 (Bắt buộc)
*   **Node.js**: >= 18.x
*   **Composer**: Latest version
*   **MySQL**: >= 8.0
*   **Redis**: (Khuyến nghị để chạy Queue & Cache tốt nhất)

### Bước 1: Khởi tạo Backend
```bash
cd web
# 1. Cài đặt các thư viện PHP
composer install

# 2. Cấu hình môi trường
cp .env.example .env
# -> Lưu ý: Cập nhật thông tin DB_DATABASE, DB_PASSWORD trong file .env

# 3. Khởi tạo dữ liệu nền tảng
php artisan key:generate
php artisan migrate --seed  # Tạo bảng và dữ liệu mẫu (Admin, Settings)

# 4. Khởi chạy Server
composer run dev
# Lệnh này sẽ tự động chạy song song: Laravel Server (8000), Queue Worker, Reverb (8080)
```

### Bước 2: Khởi tạo Frontend
```bash
cd FE
# 1. Cài đặt các thư viện JS
yarn install  # hoặc npm install

# 2. Cấu hình môi trường
cp .env.example .env
# -> Đảm bảo VITE_API_BASE_URL=http://localhost:8000/api/v1 (trỏ về Backend local)

# 3. Khởi chạy Development Server
yarn dev
```

🚀 **Truy cập ứng dụng**:
*   **Frontend**: `http://localhost:3000`
*   **API Documentation**: `http://localhost:8000/docs` (nếu đã cài đặt Swagger)

## 🤝 Quy trình phát triển (Workflow)

1.  **Branching**: Luôn tạo nhánh mới từ nhánh `dev` cho mỗi tính năng (`feat/ten-tinh-nang`) hoặc bản vá (`fix/ten-loi`).
2.  **Commit Standard**: Tuân thủ chuẩn Conventional Commits (VD: `feat: add user login`, `fix: update cart calculation`).
3.  **Pull Request**: Tạo PR và yêu cầu review code từ Tech Lead trước khi merge vào `dev`.

---
**Workforce Contract Digitization** — *Nâng tầm quản trị, tối ưu vận hành.*