# Workforce Contract Digitization - Web API

Hệ thống API E-commerce được xây dựng bằng Laravel 12.

## 📚 Tài Liệu

- **[QUICK_START.md](./../docs/QUICK_START.md)** - Hướng dẫn nhanh cho người mới bắt đầu (5 phút)
- **[DOCUMENTATION.md](./../docs/DOCUMENTATION.md)** - Tài liệu chi tiết đầy đủ về dự án
- **[CODING_CONVENTIONS.md](./../docs/CODING_CONVENTIONS.md)** - Quy ước viết code trong dự án

## 🚀 Bắt Đầu Nhanh

```bash
# Cài đặt dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Chạy migrations
php artisan migrate

# Chạy dự án
composer run dev
```

## 📖 Đọc Tài Liệu

**Nếu bạn mới vào dự án:**
1. Đọc [QUICK_START.md](./QUICK_START.md) trước (5-10 phút)
2. Sau đó đọc [DOCUMENTATION.md](./DOCUMENTATION.md) để hiểu sâu hơn

**Nếu bạn đã quen Laravel:**
- Đọc trực tiếp [DOCUMENTATION.md](./DOCUMENTATION.md)

## 🏗️ Kiến Trúc

Dự án sử dụng **Repository Pattern** + **Service Layer**:

```
Request → Route → Controller → Service → Repository → Model → Database
```

## 🛠️ Công Nghệ

- **Framework**: Laravel 12
- **PHP**: ^8.2
- **Database**: MySQL/PostgreSQL

## 📝 Cấu Trúc Dự Án

```
app/
├── Http/Controllers/    # Controllers
├── Services/            # Business Logic
├── Repositories/        # Data Access Layer
├── Models/             # Eloquent Models
└── ...
```

## 🔗 API Documentation

Xem file `openapi.yaml` để biết chi tiết về các API endpoints.

## 📞 Liên Hệ

Nếu có thắc mắc, vui lòng liên hệ team lead hoặc tạo issue.

---
