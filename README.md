# Workforce Contract Digitization

Dự án số hóa hợp đồng lao động.

## 📚 Tài Liệu

- **[ROADMAP.md](./docs/ROADMAP.md)** - Roadmap và kế hoạch phát triển dự án
- **[GITFLOW.md](./docs/GITFLOW.md)** - Quy trình Gitflow workflow
- **[DOCUMENTATION.md](./docs/DOCUMENTATION.md)** - Tài liệu kỹ thuật chi tiết
- **[QUICK_START.md](./docs/QUICK_START.md)** - Hướng dẫn nhanh cho người mới
- **[CODING_CONVENTIONS.md](./docs/CODING_CONVENTIONS.md)** - Quy ước viết code

## 🚀 Quick Start

### Development Setup

1. **Start Backend Services:**
   ```bash
   cd web
   composer run dev
   ```
   Lệnh này sẽ tự động start:
   - Laravel server (port 8000)
   - Queue worker
   - **Reverb WebSocket server (port 8080)** - Quan trọng cho realtime notifications và chat
   - Vite dev server

2. **Start Frontend (nếu chạy riêng):**
   ```bash
   cd FE
   npm run dev
   ```

### ⚠️ Lưu ý về Realtime

Để notifications và chat hoạt động realtime (không cần reload trang), bạn **PHẢI** chạy Reverb server:

```bash
cd web
php artisan reverb:start
```

Hoặc sử dụng `composer run dev` để tự động start tất cả services.

## Gitflow Workflow

Dự án này sử dụng **Gitflow Workflow** để quản lý code. Vui lòng đọc file [GITFLOW.md](./docs/GITFLOW.md) để hiểu rõ quy trình làm việc.

### Nhánh chính:
- **`prod`**: Production environment (chỉ merge qua PR)
- **`staging`**: Staging environment cho QA và demo (chỉ merge qua PR)
- **`dev`**: Development branch cho team phát triển
- **`main`**: Nhánh mặc định

### Quy tắc Commit:
**BẮT BUỘC**: Tất cả commit phải đi kèm Issue ID

Format: `<type>: <description> #<issue_id>`

Ví dụ:
```bash
git commit -m "feat: add user authentication #123"
git commit -m "fix: resolve login error #456"
```

### Bắt đầu làm việc:
```bash
# Chuyển sang nhánh dev
git checkout dev
git pull origin dev

# Tạo nhánh feature mới
git checkout -b feat/your_feature_name

# Làm việc và commit (NHỚ thêm Issue ID)
git add .
git commit -m "feat: your description #issue_id"
git push -u origin feat/your_feature_name

# Tạo Pull Request từ feat/your_feature_name vào dev
```

Xem chi tiết tại [GITFLOW.md](./docs/GITFLOW.md)
