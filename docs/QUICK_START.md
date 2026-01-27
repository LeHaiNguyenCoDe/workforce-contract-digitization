# 🚀 Hướng Dẫn Nhanh Cho Người Mới

Tài liệu này dành cho những người mới vào dự án, muốn bắt đầu làm việc nhanh chóng.

## 📋 Checklist Trước Khi Bắt Đầu

- [ ] Đã cài đặt PHP 8.2+
- [ ] Đã cài đặt Composer
- [ ] Đã cài đặt Node.js & NPM
- [ ] Đã cài đặt MySQL/PostgreSQL
- [ ] Đã clone repository

## ⚡ Setup Nhanh (5 phút)

```bash
# 1. Cài đặt dependencies
composer install
npm install

# 2. Copy file .env
cp .env.example .env

# 3. Generate key
php artisan key:generate

# 4. Cấu hình database trong .env
# DB_DATABASE=your_database
# DB_USERNAME=your_username
# DB_PASSWORD=your_password

# 5. Chạy migrations
php artisan migrate

# 6. Chạy dự án
composer run dev
```

## 🎯 Hiểu Nhanh Kiến Trúc

```
Request → Route → Controller → Service → Repository → Model → Database
```

**Quy tắc vàng**: 
- **Controller**: Chỉ nhận request, gọi service, trả response
- **Service**: Chứa business logic (nghiệp vụ)
- **Repository**: Chỉ làm việc với database
- **Model**: Định nghĩa cấu trúc dữ liệu và relationships

## 📝 Ví Dụ Thực Tế: Thêm API Endpoint Mới

### Tình huống: Thêm API lấy danh sách sản phẩm nổi bật

#### Bước 1: Thêm method vào Repository Interface
```php
// app/Repositories/Contracts/ProductRepositoryInterface.php
public function getFeatured(int $limit = 10): Collection;
```

#### Bước 2: Implement trong Repository
```php
// app/Repositories/ProductRepository.php
public function getFeatured(int $limit = 10): Collection
{
    return Product::where('featured', true)
        ->limit($limit)
        ->get();
}
```

#### Bước 3: Thêm method vào Service
```php
// app/Services/ProductService.php
public function getFeatured(int $limit = 10): array
{
    $products = $this->productRepository->getFeatured($limit);
    return $products->toArray();
}
```

#### Bước 4: Thêm method vào Controller
```php
// app/Http/Controllers/Store/ProductController.php
public function featured(Request $request): JsonResponse
{
    try {
        $limit = $request->query('limit', 10);
        $products = $this->productService->getFeatured($limit);
        
        return response()->json([
            'status' => 'success',
            'data' => $products,
        ]);
    } catch (\Exception $ex) {
        return response()->json([
            'status' => 'error',
            'message' => 'An error occurred',
        ], 500);
    }
}
```

#### Bước 5: Thêm Route
```php
// routes/api.php
Route::get('frontend/products/featured', [ProductController::class, 'featured']);
```

#### Bước 6: Test
```bash
# Test bằng curl
curl http://localhost:8000/api/v1/products/featured
```

## 🔍 Các File Quan Trọng Cần Nhớ

| File | Mục đích |
|------|----------|
| `routes/api.php` | Định nghĩa tất cả API endpoints |
| `app/Http/Controllers/` | Xử lý HTTP requests |
| `app/Services/` | Business logic |
| `app/Repositories/` | Database operations |
| `app/Models/` | Eloquent models |
| `.env` | Cấu hình môi trường |

## 🎓 Học Laravel Nhanh

### 1. Eloquent ORM (Quan trọng nhất)
```php
// Lấy tất cả
Product::all();

// Lấy theo điều kiện
Product::where('price', '>', 100)->get();

// Lấy một record
Product::find(1);

// Tạo mới
Product::create(['name' => 'Product 1']);

// Cập nhật
$product = Product::find(1);
$product->update(['name' => 'New Name']);

// Xóa
$product->delete();

// Relationships
$product->category; // BelongsTo
$product->images;    // HasMany
```

### 2. Validation
```php
$request->validate([
    'email' => 'required|email',
    'password' => 'required|min:8',
]);
```

### 3. Response JSON
```php
return response()->json([
    'status' => 'success',
    'data' => $data,
], 200);
```

## 🐛 Debug Nhanh

### Xem log lỗi
```bash
tail -f storage/logs/laravel.log
```

### Xem routes
```bash
php artisan route:list
```

### Clear cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### Tinker (Console tương tác)
```bash
php artisan tinker
# Trong tinker:
>>> Product::count()
>>> $product = Product::first()
>>> $product->name
```

## 📚 Tài Liệu Đọc Thêm

1. **DOCUMENTATION.md**: Tài liệu chi tiết đầy đủ
2. **Laravel Docs**: https://laravel.com/docs
3. **Code trong dự án**: Đọc code của các tính năng hiện có

## ❓ Câu Hỏi Thường Gặp

**Q: Làm sao biết nên đặt code ở đâu?**
A: 
- Logic nghiệp vụ → Service
- Query database → Repository  
- Xử lý HTTP → Controller

**Q: Khi nào dùng FormRequest?**
A: Khi validation phức tạp hoặc cần tái sử dụng validation.

**Q: Khi nào dùng Exception?**
A: Khi có lỗi nghiệp vụ cần throw (NotFoundException, BusinessLogicException).

**Q: Làm sao test API?**
A: Dùng Postman, Insomnia, hoặc curl.

## 🎯 Mục Tiêu Học Tập

- [ ] Hiểu được luồng Request → Response
- [ ] Biết cách thêm endpoint mới
- [ ] Hiểu Repository Pattern
- [ ] Biết cách debug lỗi
- [ ] Đọc được code hiện có

**Chúc bạn học tốt! 💪**

