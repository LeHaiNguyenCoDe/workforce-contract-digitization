# Warehouse Management - BRD Implementation

## Tổng quan

Đã xây dựng lại toàn bộ logic quản lý kho hàng theo Business Rule Document (BRD) với các nguyên tắc vàng:
- **No QC_PASS → No Inventory**
- **No Batch → No Trace**
- **No Log → No Audit**
- **System-driven > Human-driven**

## Các thay đổi chính

### 1. Database Schema

#### Migrations mới:
- `2025_12_26_000001_create_inbound_batches_table.php`: Tạo bảng `inbound_batches`, `inbound_batch_items`, và `product_supplier` (nhiều-nhiều)
- `2025_12_26_000002_update_stocks_for_brd.php`: Cập nhật `stocks` và đổi tên `stock_movements` → `inventory_logs`
- `2025_12_26_000003_update_quality_checks_for_brd.php`: Cập nhật `quality_checks` theo BRD
- `2025_12_26_000004_remove_stock_qty_from_products.php`: Xóa `stock_qty` khỏi `products` (BR-01.1)

#### Cấu trúc mới:

**inbound_batches:**
- `batch_number`: Số lô nhập (unique)
- `warehouse_id`: Kho hàng
- `supplier_id`: Nhà cung cấp (BR-02.1)
- `status`: pending, received, qc_in_progress, qc_completed, completed, cancelled
- `received_date`: Ngày nhận hàng
- `created_by`: Người tạo

**inbound_batch_items:**
- `inbound_batch_id`: Liên kết với batch
- `product_id`, `product_variant_id`: Sản phẩm
- `quantity_received`: Số lượng nhận (BR-02.2)

**stocks:**
- `inbound_batch_id`: Truy vết batch (BR-04.2)
- `quality_check_id`: Truy vết QC (BR-04.2)
- `available_quantity`: Tồn kho có thể xuất (BR-06.1)
- Đã xóa: `quality_status`, `quality_notes` (chuyển sang QC)

**inventory_logs** (trước đây là `stock_movements`):
- `movement_type`: inbound, qc_pass, qc_fail, outbound, adjust, return
- `quantity_before`, `quantity_after`: Trước và sau khi thay đổi
- `user_id`: Người thao tác
- `reason`: Lý do điều chỉnh (BR-05.2)
- `inbound_batch_id`, `quality_check_id`: Truy vết

**quality_checks:**
- `inbound_batch_id`: Bắt buộc (BR-03.1)
- `warehouse_id`: Kho hàng
- `status`: pass, fail, partial (BR-03.3)
- `quantity_passed`, `quantity_failed`: Số lượng PASS/FAIL
- `is_rollback`: Đánh dấu rollback (BR-11.1)
- Đã xóa: `batch_number` (dùng `inbound_batch_id`)

### 2. Models

#### Models mới:
- `InboundBatch`: Quản lý lô nhập
- `InboundBatchItem`: Chi tiết sản phẩm trong lô nhập
- `InventoryLog`: Thay thế `StockMovement` (BR-09.2)

#### Models đã cập nhật:
- `Stock`: Thêm `inbound_batch_id`, `quality_check_id`, `available_quantity`
- `QualityCheck`: Liên kết với `InboundBatch`, status mới (pass/fail/partial)
- `Product`: Xóa `stock_qty`, thêm relationship `suppliers()` (nhiều-nhiều)

### 3. Services & Repositories

#### WarehouseService - Logic mới:

**Inbound Batch (BR-02):**
- `createInboundBatch()`: Tạo batch mới (BR-02.1)
- `receiveInboundBatch()`: Nhận hàng, chuyển status → RECEIVED (BR-02.2)
- `getInboundBatches()`: Lấy danh sách batches

**Quality Check (BR-03):**
- `createQualityCheck()`: Tạo QC trên Batch (BR-03.1)
  - Kiểm tra batch chưa có QC chính thức (BR-03.2)
  - Tự động tạo Inventory nếu PASS/PARTIAL (BR-03.4, BR-04.1)
  - Không tạo Inventory nếu FAIL (BR-03.4)

**Stock Management (BR-04, BR-05, BR-06):**
- `adjustStock()`: Điều chỉnh tồn kho (BR-05.1, BR-05.2, BR-05.3)
  - Bắt buộc có lý do
  - Ghi log đầy đủ
- `outboundStock()`: Xuất kho (BR-06.1, BR-06.2, BR-06.3)
  - Chỉ xuất từ `available_quantity`
  - Không cho xuất vượt tồn
  - Hỗ trợ FIFO/LIFO/FEFO (có thể config)

**Inventory Logs (BR-09):**
- `getInventoryLogs()`: Lấy lịch sử biến động
- Mọi thao tác đều ghi log (BR-09.2)
- Không được xóa log (BR-09.1)

#### Repositories:
- `InventoryLogRepository`: Thay thế `StockMovementRepository`
- Cập nhật `AppServiceProvider` để bind interface mới

### 4. Controllers

#### WarehouseController - Endpoints mới:

**Inbound Batch:**
- `POST /api/v1/admin/warehouses/inbound-batches`: Tạo batch
- `GET /api/v1/admin/warehouses/inbound-batches`: Danh sách batches
- `GET /api/v1/admin/warehouses/inbound-batches/{id}`: Chi tiết batch
- `POST /api/v1/admin/warehouses/inbound-batches/{id}/receive`: Nhận hàng

**Quality Check:**
- `POST /api/v1/admin/warehouses/quality-checks`: Tạo QC (chỉ trên Batch)
- `GET /api/v1/admin/warehouses/quality-checks`: Danh sách QC

**Stock Management:**
- `POST /api/v1/admin/warehouses/{warehouse}/stocks/adjust`: Điều chỉnh tồn kho
- `POST /api/v1/admin/warehouses/{warehouse}/stocks/outbound`: Xuất kho

**Inventory Logs:**
- `GET /api/v1/admin/warehouses/{warehouse}/inventory-logs`: Lịch sử biến động

### 5. Business Rules Implementation

#### ✅ BR-01: Quản lý sản phẩm
- Product không chứa thông tin tồn kho
- Product có thể thuộc nhiều NCC (bảng pivot)

#### ✅ BR-02: Nhập kho
- Mỗi lần nhập phải tạo Inbound Batch
- Batch chỉ ghi nhận số lượng, chưa cộng tồn khi RECEIVED
- Batch không được sửa sau khi QC bắt đầu

#### ✅ BR-03: Kiểm tra chất lượng
- QC bắt buộc theo Batch
- Một Batch chỉ có 1 QC chính thức
- QC phân loại: PASS, FAIL, PARTIAL
- QC FAIL không tạo Inventory

#### ✅ BR-04: Tạo & quản lý tồn kho
- Inventory chỉ tạo khi QC = PASS
- Inventory gắn nguồn Batch & QC
- Inventory không được sửa trực tiếp

#### ✅ BR-05: Điều chỉnh tồn kho
- Chỉ role quản lý (TODO: implement middleware)
- Bắt buộc có lý do
- Mọi điều chỉnh đều ghi log

#### ✅ BR-06: Xuất kho
- Chỉ xuất từ Available Inventory
- Không cho xuất vượt tồn
- Hỗ trợ FIFO/LIFO/FEFO

#### ✅ BR-09: Log & Audit
- Không được xóa Inventory Log
- Mọi biến động đều có log

## Workflow mới

### Quy trình nhập kho:
1. **Tạo Inbound Batch** → Status: PENDING
2. **Nhận hàng** → Status: RECEIVED (chỉ ghi nhận, chưa cộng tồn)
3. **QC** → Status: QC_IN_PROGRESS → QC_COMPLETED
   - Nếu PASS/PARTIAL → Tự động tạo Inventory
   - Nếu FAIL → Không tạo Inventory
4. **Hoàn tất** → Status: COMPLETED

### Quy trình xuất kho:
1. Kiểm tra `available_quantity`
2. Xuất theo FIFO/LIFO/FEFO
3. Trừ cả `quantity` và `available_quantity`
4. Ghi log

### Quy trình điều chỉnh:
1. Kiểm tra phân quyền (TODO)
2. Bắt buộc nhập lý do
3. Cập nhật tồn kho
4. Ghi log với `quantity_before` và `quantity_after`

## TODO / Cần hoàn thiện

1. **Phân quyền (BR-05.1, BR-10):**
   - Implement middleware kiểm tra role cho điều chỉnh tồn kho
   - Implement phân quyền cho QC và điều chỉnh

2. **FIFO/LIFO/FEFO (BR-06.3):**
   - Cấu hình rule xuất kho
   - Implement logic xuất theo rule

3. **Hàng trả/Hoàn (BR-07):**
   - Tạo batch trả hàng
   - QC lại hàng trả

4. **Kiểm kê (BR-08):**
   - Tạo phiếu kiểm kê
   - Xử lý chênh lệch

5. **Rollback QC (BR-11.1):**
   - Quy trình rollback QC sai
   - Phê duyệt rollback

6. **Báo cáo & Cảnh báo (BR-12):**
   - Cảnh báo tồn kho thấp
   - Cảnh báo NCC có tỷ lệ QC FAIL cao

## Lưu ý khi chạy migrations

1. Chạy migrations theo thứ tự:
   ```bash
   php artisan migrate
   ```

2. Nếu có dữ liệu cũ:
   - Cần migrate dữ liệu từ `stock_movements` → `inventory_logs`
   - Cần tạo `inbound_batches` cho các lô nhập cũ (nếu có)

3. Kiểm tra foreign keys:
   - Đảm bảo `inbound_batches` được tạo trước `quality_checks`
   - Đảm bảo `users` table tồn tại

## Testing

Cần test các scenarios:
- Tạo batch → Nhận hàng → QC PASS → Kiểm tra Inventory
- Tạo batch → Nhận hàng → QC FAIL → Kiểm tra không có Inventory
- Điều chỉnh tồn kho → Kiểm tra log
- Xuất kho vượt tồn → Kiểm tra error
- Xuất kho bình thường → Kiểm tra log và tồn kho


