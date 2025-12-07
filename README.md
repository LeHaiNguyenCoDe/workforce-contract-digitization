# Workforce Contract Digitization

Dự án số hóa hợp đồng lao động.

## 🌳 Gitflow Workflow

Dự án này sử dụng **Gitflow Workflow** để quản lý code. Vui lòng đọc file [GITFLOW.md](./GITFLOW.md) để hiểu rõ quy trình làm việc.

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

Xem chi tiết tại [GITFLOW.md](./GITFLOW.md)