# Gitflow Workflow - Hướng dẫn Quy trình Làm việc

## Tổng quan

Dự án này sử dụng **Gitflow Workflow** để quản lý code một cách có tổ chức và an toàn. Tất cả các commit phải đi kèm với **Issue ID**.

## Cấu trúc Nhánh

### Nhánh chính (Main Branches)

1. **`prod`** (Production)
   - Chứa code chạy trên môi trường production
   - Chỉ merge sau khi đã kiểm tra kỹ lưỡng
   - **KHÔNG được push trực tiếp**, chỉ merge qua Pull Request

2. **`staging`** (Staging)
   - Dùng cho việc kiểm tra QA và demo
   - Merge từ `dev` khi các feature đã ổn định
   - **KHÔNG được push trực tiếp**, chỉ merge qua Pull Request

3. **`dev`** (Development)
   - Nhánh chính cho team phát triển
   - Nơi tất cả các feature được hợp nhất trước khi chuyển sang staging
   - Có thể push trực tiếp (nhưng khuyến khích dùng PR)

4. **`main`**
   - Nhánh mặc định của repository
   - Có thể dùng làm nhánh backup hoặc documentation

### Nhánh hỗ trợ (Support Branches)

1. **`feat/<feature_name>`** (Feature Branches)
   - Tạo từ `dev`
   - Format: `feat/login_page`, `feat/user_dashboard`, `feat/payment_integration`
   - Dùng để phát triển tính năng mới

2. **`hotfix/<hotfix_name>`** (Hotfix Branches)
   - Tạo từ `prod`
   - Format: `hotfix/fix_login_error`, `hotfix/security_patch`
   - Dùng để sửa lỗi khẩn cấp trên production

3. **`fix/<bug_name>`** (Bugfix Branches)
   - Tạo từ `dev`
   - Format: `fix/memory_leak`, `fix/validation_error`
   - Dùng để sửa lỗi thông thường

## Quy trình Làm việc

### 1. Phát triển Feature mới

```bash
# Bước 1: Chuyển sang nhánh dev và cập nhật
git checkout dev
git pull origin dev

# Bước 2: Tạo nhánh feature mới
git checkout -b feat/homepage

# Bước 3: Làm việc và commit (NHỚ thêm Issue ID)
git add .
git commit -m "feat: add homepage layout #123"

# Bước 4: Push nhánh feature lên remote
git push -u origin feat/homepage

# Bước 5: Tạo Pull Request từ feat/homepage vào dev trên GitHub/GitLab
# Bước 6: Sau khi review và approve, merge PR vào dev
# Bước 7: Xóa nhánh feature sau khi merge (tùy chọn)
```

### 2. Merge dev vào staging

```bash
# Bước 1: Chuyển sang staging và cập nhật
git checkout staging
git pull origin staging

# Bước 2: Merge dev vào staging
git merge dev

# Bước 3: Push lên remote (hoặc tạo PR)
git push origin staging
```

### 3. Deploy lên Production

```bash
# Bước 1: Chuyển sang prod và cập nhật
git checkout prod
git pull origin prod

# Bước 2: Merge từ staging hoặc dev (sau khi đã test kỹ)
git merge staging  # hoặc git merge dev

# Bước 3: Push lên remote (hoặc tạo PR)
git push origin prod
```

### 4. Xử lý Hotfix (Sửa lỗi khẩn cấp)

```bash
# Bước 1: Tạo nhánh hotfix từ prod
git checkout prod
git pull origin prod
git checkout -b hotfix/fix_login_error

# Bước 2: Sửa lỗi và commit
git add .
git commit -m "hotfix: fix login authentication error #456"

# Bước 3: Push nhánh hotfix
git push -u origin hotfix/fix_login_error

# Bước 4: Tạo PR từ hotfix vào prod
# Bước 5: Sau khi merge vào prod, merge vào staging và dev để đồng bộ

# Merge vào staging
git checkout staging
git merge prod
git push origin staging

# Merge vào dev
git checkout dev
git merge prod
git push origin dev
```

## Quy tắc Commit Message

### Format chuẩn:
```
<type>: <description> #<issue_id>
```

### Các loại commit:
- `feat`: Thêm tính năng mới
- `fix`: Sửa lỗi
- `hotfix`: Sửa lỗi khẩn cấp trên production
- `docs`: Cập nhật tài liệu
- `style`: Thay đổi format code (không ảnh hưởng logic)
- `refactor`: Refactor code
- `test`: Thêm hoặc sửa test
- `chore`: Cập nhật build process, dependencies, etc.

### Ví dụ:
```bash
git commit -m "feat: add user authentication #123"
git commit -m "fix: resolve memory leak in image processing #456"
git commit -m "hotfix: fix payment gateway timeout #789"
git commit -m "docs: update API documentation #101"
git commit -m "refactor: optimize database queries #202"
```

## Lưu ý quan trọng

1. **Luôn thêm Issue ID vào commit message** - Đây là yêu cầu bắt buộc
2. **KHÔNG push trực tiếp vào `prod` và `staging`** - Chỉ merge qua Pull Request
3. **Luôn pull trước khi push** để tránh conflict
4. **Review code trước khi merge** - Ít nhất 1 người review
5. **Sau hotfix, nhớ merge vào staging và dev** để đồng bộ code
6. **Xóa nhánh feature/hotfix sau khi merge** để giữ repo gọn gàng