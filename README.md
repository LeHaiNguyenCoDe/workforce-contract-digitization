# Workforce Contract Digitization

Nền tảng số hóa hợp đồng lao động: thay thế giấy tờ bằng hệ thống lưu trữ tập trung, hỗ trợ ký điện tử (E‑Sign), cảnh báo hết hạn, phân loại hồ sơ, và quy trình phê duyệt linh hoạt.

---

## 1) Kiến trúc & Tech Stack
- Frontend: Vue.js 3 + Vite (TypeScript optional), Pinia, Vue Router, Axios
- Backend: Laravel (PHP 8.2+), MySQL, Redis (cache/queue), Sanctum/JWT (tùy chọn)
- CI/CD: GitHub Actions (Gitflow: feature → develop → main)
- Container: Docker + Docker Compose, Nginx + PHP-FPM

---

## 2) Yêu cầu hệ thống (local, không dùng Docker)
- Node.js 18+ và npm 9+
- PHP 8.2+, Composer 2+
- MySQL 8+ (hoặc MariaDB tương thích)
- Redis 7+ (tuỳ chọn cho cache/queue)

Nếu dùng Docker, chỉ cần Docker Desktop (Docker Engine + Compose).

---

## 3) Cấu trúc thư mục khuyến nghị

### Frontend (Vue 3 + Vite)
```
frontend/
└─ src/
   ├─ main.ts                  # Điểm vào ứng dụng
   ├─ App.vue                  # Root component
   ├─ router/
   │  └─ index.ts              # Định tuyến, guards
   ├─ store/                   # Pinia stores (modules)
   ├─ pages/                   # Page-level views
   ├─ components/              # Reusable UI components
   ├─ services/
   │  ├─ http.ts               # Axios instance (baseURL từ env)
   │  └─ api/                  # API service layer (contract, user,...)
   ├─ composables/             # Composition utilities (useAuth,...)
   ├─ assets/                  # Ảnh, font,…
   ├─ styles/                  # SCSS/CSS chung (variables, tailwind, ...)
   └─ env.d.ts                 # (TS) khai báo module
```

### Backend (Laravel)
```
backend/
├─ app/
│  ├─ Models/           # Eloquent models (Contract, Employee, ...)
│  ├─ Http/
│  │  ├─ Controllers/   # API Controllers
│  │  ├─ Middleware/
│  │  └─ Requests/      # FormRequest validation
│  ├─ Policies/
│  ├─ Services/         # Business services (ký số, PDF, ...)
│  └─ Console/
├─ database/
│  ├─ migrations/       # Tạo bảng, sửa bảng
│  ├─ seeders/          # Dữ liệu mẫu
│  └─ factories/
├─ routes/
│  ├─ api.php           # REST API endpoints
│  └─ web.php
└─ config/, storage/, bootstrap/, public/
```

---

## 4) Cấu hình môi trường

### Frontend (.env)
- Sử dụng biến tiền tố Vite: `VITE_API_URL` làm base URL cho Axios.

Ví dụ (file `frontend/.env.development`):
```
VITE_API_URL=http://localhost:8000/api/v1
```

### Backend (.env)
Sao chép từ `.env.example`:
```
cp .env.example .env
php artisan key:generate
```
Cấu hình ví dụ:
```
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=workforce
DB_USERNAME=root
DB_PASSWORD=

CACHE_DRIVER=redis  # nếu dùng
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

---

## 5) Chạy dự án (2 cách)

### Cách A: Local (không Docker)
1) Frontend
```
cd frontend
npm ci
npm run dev        # http://localhost:5173 (mặc định của Vite)
```
Cập nhật `VITE_API_URL` trỏ tới backend local.

2) Backend (Laravel)
```
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed    # tạo bảng + dữ liệu mẫu (nếu có)
php artisan serve             # http://127.0.0.1:8000
```

### Cách B: Docker Compose
Sử dụng cấu hình đã chuẩn bị sẵn:
```
# 1) Chuẩn bị biến môi trường (xem .env.example)
# 2) Khởi chạy stack (staging profile)
docker compose -f docker-compose.staging.yml up -d

# 3) Migrate DB (chạy bên trong container app)
docker compose -f docker-compose.staging.yml exec app php artisan migrate --force
```
- Nginx: 80/443 (map theo compose)
- App PHP-FPM: 9000 (internal)
- MySQL: 3306 (hoặc 3307 nếu map khác)

---

## 6) Cấu hình src (frontend) chi tiết
- API baseURL: cấu hình trong `src/services/http.ts`:
```ts
import axios from 'axios'

export const http = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  withCredentials: true,
})
```
- Tầng service: tạo các file trong `src/services/api/` (ví dụ `contracts.ts`) để gom API logic, không gọi Axios trực tiếp trong component.
- State management (Pinia): mỗi domain 1 store trong `src/store/` (ví dụ `useContractStore.ts`).
- Router guards: xác thực/ủy quyền tại `src/router/index.ts` (VD kiểm tra token/role trước khi vào route).
- UI components: đặt trong `src/components/`, theo quy ước PascalCase, tách nhỏ, tái sử dụng.
- Styles: dùng Tailwind/PostCSS/SCSS (tùy), tập trung biến/mixin trong `src/styles/`.
- Lint & format: tuân thủ ESLint + Prettier; tránh logic phức tạp trong component.

---

## 7) Scripts hữu ích

### Frontend (npm)
- `npm run dev` – chạy Vite dev server
- `npm run build` – build production
- `npm run preview` – preview build
- `npm run lint` – ESLint auto-fix
- `npm run test:unit` – Vitest (unit)
- `npm run test:coverage` – báo cáo coverage

### Backend (composer / artisan)
- `composer test` hoặc `php artisan test` – chạy test
- `composer lint` / `pint` – định dạng code PHP
- `composer phpcs` – kiểm tra PSR12
- `composer phpstan` – static analysis
- `php artisan migrate`, `db:seed`, `migrate:rollback`

---

## 8) Cơ bản về API & Auth
- Base API: `${APP_URL}/api/v1`
- Auth: dùng Sanctum/JWT (tuỳ chọn) → frontend lưu token trong memory/secure storage; thiết lập interceptor Axios để đính kèm token.
- Versioning: đặt prefix `/api/v1` trong `routes/api.php`.

---

## 9) CI/CD rút gọn
- PR vào `develop` → tự động Lint/Test (chặn merge nếu fail).
- Merge vào `develop` → build & deploy Staging (docker image push GHCR, deploy qua SSH).
- Merge vào `main`/tạo tag `v*.*.*` → build Production; deploy Production cần manual approval/tag.

Chi tiết xem trong `.github/workflows/` và thư mục `docs/`.

---

## 10) Troubleshooting nhanh
- Frontend không gọi được API: kiểm tra `VITE_API_URL` và CORS (Laravel: config/cors.php hoặc middleware).
- Migration lỗi: kiểm tra kết nối DB (.env), quyền user, phiên bản MySQL.
- Docker port conflict: đổi port mapping trong docker-compose.*.yml.
- Quyền thư mục: đảm bảo `storage/` và `bootstrap/cache` có quyền ghi (www-data trong container).

---

## 11) Lộ trình tiếp theo
- Module Hợp đồng: CRUD hợp đồng, upload file, ký số, trạng thái & history.
- Nhân sự: hồ sơ nhân viên, phân quyền theo vai trò (admin/HR/manager/employee).
- Báo cáo & nhắc hạn: cron + queue, gửi email/Slack.

---

Copyright © 2025 Workforce Contract Digitization.