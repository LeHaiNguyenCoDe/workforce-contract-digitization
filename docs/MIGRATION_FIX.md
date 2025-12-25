# Hướng dẫn chạy Migration

## Vấn đề

Các migrations mới chưa được chạy, dẫn đến lỗi khi gọi API:
- `inbound_batches` table chưa tồn tại
- `inventory_logs` table chưa tồn tại (chưa đổi tên từ `stock_movements`)
- Các cột mới trong `stocks` và `quality_checks` chưa được thêm

## Giải pháp

### 1. Chạy migrations

```bash
cd web
php artisan migrate
```

### 2. Nếu gặp lỗi foreign key

Nếu gặp lỗi foreign key constraint (do thứ tự migration), có thể cần chạy từng migration:

```bash
php artisan migrate --path=database/migrations/2025_12_26_000001_create_inbound_batches_table.php
php artisan migrate --path=database/migrations/2025_12_26_000002_update_stocks_for_brd.php
php artisan migrate --path=database/migrations/2025_12_26_000003_update_quality_checks_for_brd.php
php artisan migrate --path=database/migrations/2025_12_26_000004_remove_stock_qty_from_products.php
```

### 3. Nếu có dữ liệu cũ trong stock_movements

Nếu đã có dữ liệu trong `stock_movements`, migration sẽ tự động:
- Đổi tên bảng thành `inventory_logs`
- Thêm các cột mới
- Đổi tên cột `type` → `movement_type`
- Cập nhật enum values

### 4. Kiểm tra sau khi migrate

```bash
php artisan migrate:status
```

Tất cả migrations phải có status "Ran".

### 5. Nếu vẫn gặp lỗi

Kiểm tra log:
```bash
tail -f storage/logs/laravel.log
```

Hoặc xem chi tiết lỗi trong response API.

