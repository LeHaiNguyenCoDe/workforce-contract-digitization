# SweetAlert2 Composable

Composable dùng chung cho SweetAlert2 trong dự án.

## Cài đặt

```bash
npm install sweetalert2
```

## Cách sử dụng

### Import

```typescript
import { useSwal } from '@/shared/utils'
```

### Các phương thức

#### 1. Success Alert
```typescript
const swal = useSwal()
await swal.success('Thành công!')
await swal.success('Lưu thành công!', 'Thông báo')
```

#### 2. Error Alert
```typescript
await swal.error('Có lỗi xảy ra!')
await swal.error('Không thể kết nối server', 'Lỗi kết nối')
```

#### 3. Warning Alert
```typescript
await swal.warning('Vui lòng kiểm tra lại!')
```

#### 4. Info Alert
```typescript
await swal.info('Thông tin quan trọng')
```

#### 5. Confirmation Dialog
```typescript
const confirmed = await swal.confirm('Bạn có chắc chắn?')
if (confirmed) {
    // User clicked "Xác nhận"
} else {
    // User clicked "Hủy"
}
```

#### 6. Delete Confirmation
```typescript
const confirmed = await swal.confirmDelete('Bạn có chắc chắn muốn xóa?')
if (confirmed) {
    // Delete item
}
```

#### 7. Custom Alert
```typescript
await swal.fire({
    title: 'Custom Title',
    text: 'Custom message',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'OK',
    cancelButtonText: 'Cancel'
})
```

#### 8. Loading Alert
```typescript
swal.loading('Đang xử lý...')
// Do something
swal.close() // Close loading
```

## Ví dụ trong component

```vue
<script setup lang="ts">
import { useSwal } from '@/shared/utils'

const swal = useSwal()

const handleSave = async () => {
    try {
        // Save logic
        await swal.success('Lưu thành công!')
    } catch (error: any) {
        await swal.error(error.message || 'Có lỗi xảy ra!')
    }
}

const handleDelete = async (id: number) => {
    const confirmed = await swal.confirmDelete('Bạn có chắc chắn muốn xóa?')
    if (!confirmed) return
    
    try {
        // Delete logic
        await swal.success('Đã xóa thành công!')
    } catch (error: any) {
        await swal.error('Xóa thất bại!')
    }
}
</script>
```

## Thay thế alert() và confirm()

### Trước:
```typescript
alert('Thông báo')
if (confirm('Xác nhận?')) { ... }
```

### Sau:
```typescript
await swal.info('Thông báo')
const confirmed = await swal.confirm('Xác nhận?')
if (confirmed) { ... }
```

