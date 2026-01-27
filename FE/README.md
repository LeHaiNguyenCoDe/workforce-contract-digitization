# Workforce Contract Digitization - Frontend

Vue 3 + TypeScript frontend cho hệ thống E-Commerce.

## ⚡ Công nghệ

- **Vue 3** - Framework chính
- **TypeScript** - Type safety
- **Vite** - Build tool
- **Vue Router** - Routing
- **Pinia** - State management
- **Axios** - HTTP client

## 📁 Cấu trúc thư mục

```
src/
├── api/           # API client và types
├── modules/       # Feature modules
│   ├── auth/      # Authentication
│   ├── products/  # Products
│   ├── categories/# Categories
│   ├── cart/      # Shopping cart
│   ├── orders/    # Orders
│   ├── wishlist/  # Wishlist
│   ├── articles/  # Articles
│   ├── promotions/# Promotions
│   ├── profile/   # User profile
│   └── loyalty/   # Loyalty program
├── shared/        # Shared components & layouts
├── stores/        # Pinia stores
├── router/        # Vue Router config
└── assets/        # CSS & static files
```

## 🚀 Bắt đầu

### Cài đặt dependencies

```bash
cd FE
npm install
```

### Chạy development server

```bash
npm run dev
```

Mở http://localhost:5173 để xem kết quả.

### Build production

```bash
npm run build
```

## 🔗 API Configuration

API được proxy trong development:

- `/api` → `http://workforce_contract_digitization.io`

Xem `vite.config.ts` để cấu hình.

## 📝 Notes

- Dark theme với glassmorphism design
- Responsive trên mọi thiết bị
- Session-based authentication
