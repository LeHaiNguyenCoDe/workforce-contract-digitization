# 📚 Tài Liệu Hướng Dẫn Dự Án Laravel

## Mục Lục
1. [Giới Thiệu Dự Án](#giới-thiệu-dự-án)
2. [Cài Đặt và Setup](#cài-đặt-và-setup)
3. [Kiến Trúc Dự Án](#kiến-trúc-dự-án)
4. [Hướng Dẫn Làm Việc Với Từng Phần](#hướng-dẫn-làm-việc-với-từng-phần)
5. [Quy Trình Phát Triển Tính Năng Mới](#quy-trình-phát-triển-tính-năng-mới)
6. [Best Practices](#best-practices)
7. [Troubleshooting](#troubleshooting)

---

## Giới Thiệu Dự Án

Đây là một dự án **Laravel 12** xây dựng hệ thống **E-commerce API** với các tính năng:
- Quản lý sản phẩm, danh mục
- Giỏ hàng (Cart) dựa trên Session
- Đơn hàng (Orders)
- Đánh giá sản phẩm (Reviews)
- Chương trình khách hàng thân thiết (Loyalty)
- Quản lý kho (Warehouse)
- Hỗ trợ đa ngôn ngữ (i18n)
- Phân quyền Admin/Customer

### Công Nghệ Sử Dụng
- **Framework**: Laravel 12
- **PHP**: ^8.2
- **Database**: MySQL/PostgreSQL (tùy cấu hình)
- **Architecture**: Repository Pattern + Service Layer

---

## Cài Đặt và Setup

### Yêu Cầu Hệ Thống
- PHP >= 8.2
- Composer
- Node.js & NPM
- MySQL/PostgreSQL
- Git

### Bước 1: Clone Repository
```bash
git clone <repository-url>
cd web
```

### Bước 2: Cài Đặt Dependencies
```bash
# Cài đặt PHP dependencies
composer install

# Cài đặt Node dependencies
npm install
```

### Bước 3: Cấu Hình Environment
```bash
# Copy file .env.example thành .env
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Bước 4: Cấu Hình Database
Mở file `.env` và cấu hình:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Bước 5: Chạy Migrations và Seeders
```bash
# Chạy migrations
php artisan migrate

# Chạy seeders (nếu có)
php artisan db:seed
```

### Bước 6: Chạy Dự Án
```bash
# Cách 1: Sử dụng script có sẵn (chạy server, queue, vite cùng lúc)
composer run dev

# Cách 2: Chạy từng service riêng
php artisan serve          # Chạy Laravel server
php artisan queue:listen   # Chạy queue worker
npm run dev               # Chạy Vite dev server
```

### Bước 7: Truy Cập
- API: `http://localhost:8000`
- API Documentation: Xem file `openapi.yaml`

---

## Kiến Trúc Dự Án

Dự án sử dụng **Repository Pattern** kết hợp với **Service Layer** để tách biệt logic và dễ bảo trì.

### Luồng Xử Lý Request

```
Request → Route → Middleware → Controller → Service → Repository → Model → Database
                                                      ↓
                                              Response ← Controller
```

### Cấu Trúc Thư Mục

```
app/
├── Console/Commands/          # Artisan commands
├── Exceptions/                # Custom exceptions
├── Helpers/                   # Helper functions
├── Http/
│   ├── Controllers/           # Controllers (xử lý HTTP requests)
│   ├── Middleware/            # Middleware (authentication, authorization)
│   └── Requests/              # Form Request Validation
├── Logging/                   # Custom logging channels
├── Models/                    # Eloquent Models
├── Providers/                 # Service Providers
├── Repositories/              # Repository Pattern
│   ├── Contracts/             # Repository Interfaces
│   └── [Repository Classes]   # Repository Implementations
├── Services/                  # Business Logic Layer
└── Traits/                    # Reusable Traits
```

### Giải Thích Từng Layer

1. **Routes** (`routes/api.php`): Định nghĩa các endpoint API
2. **Middleware**: Xử lý authentication, authorization trước khi vào controller
3. **Controller**: Nhận request, gọi service, trả về response
4. **Service**: Chứa business logic, xử lý nghiệp vụ
5. **Repository**: Tương tác với database, thực hiện CRUD operations
6. **Model**: Eloquent ORM, định nghĩa relationships, attributes

---

## Hướng Dẫn Làm Việc Với Từng Phần

### 1. Routes (Định Nghĩa API Endpoints)

**File**: `routes/api.php`

#### Cấu Trúc Routes
```php
Route::prefix('v1')->middleware([StartSession::class])->group(function () {
    // Public routes (không cần đăng nhập)
    Route::group(['prefix' => 'frontend'], function () {
        Route::get('products', [ProductController::class, 'index']);
    });
    
    // Authenticated routes (cần đăng nhập)
    Route::group(['prefix' => 'frontend', 'middleware' => [Authenticate::class]], function () {
        Route::get('orders', [OrderController::class, 'index']);
    });
    
    // Admin routes (cần admin role)
    Route::group([
        'prefix' => 'admin',
        'middleware' => [Authenticate::class, AdminMiddleware::class]
    ], function () {
        Route::apiResource('products', ProductController::class);
    });
});
```

#### Cách Thêm Route Mới
```php
// Trong routes/api.php
Route::group(['prefix' => 'frontend'], function () {
    // GET endpoint
    Route::get('my-endpoint', [MyController::class, 'myMethod']);
    
    // POST endpoint
    Route::post('my-endpoint', [MyController::class, 'store']);
    
    // Resource routes (CRUD đầy đủ)
    Route::apiResource('resources', ResourceController::class);
});
```

**Lưu ý**:
- Sử dụng `prefix` để nhóm các routes liên quan
- Áp dụng middleware phù hợp (Authenticate, AdminMiddleware)
- Đặt tên route rõ ràng, theo RESTful convention

---

### 2. Controllers (Xử Lý HTTP Requests)

**Vị trí**: `app/Http/Controllers/`

#### Cấu Trúc Controller Cơ Bản
```php
<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->query('per_page', 12);
            $products = $this->productService->getAll($perPage);
            
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
}
```

#### Quy Tắc Viết Controller

1. **Dependency Injection**: Inject Service vào constructor
2. **Xử lý Exception**: Luôn wrap trong try-catch
3. **Response Format**: Luôn trả về format JSON thống nhất
   ```php
   {
       "status": "success|error",
       "message": "Message text",
       "data": {...}
   }
   ```
4. **HTTP Status Codes**: 
   - 200: Success
   - 201: Created
   - 400: Bad Request
   - 401: Unauthorized
   - 404: Not Found
   - 422: Validation Error
   - 500: Server Error

#### Ví Dụ Controller Hoàn Chỉnh
```php
public function store(Request $request): JsonResponse
{
    try {
        // 1. Validate input (có thể dùng FormRequest)
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|integer|min:0',
        ]);
        
        // 2. Gọi service để xử lý business logic
        $product = $this->productService->create($request->all());
        
        // 3. Trả về response
        return response()->json([
            'status' => 'success',
            'message' => 'Product created successfully',
            'data' => $product,
        ], 201);
    } catch (ValidationException $ex) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation error',
            'errors' => $ex->errors(),
        ], 422);
    } catch (\Exception $ex) {
        return response()->json([
            'status' => 'error',
            'message' => 'An error occurred',
        ], 500);
    }
}
```

---

### 3. Services (Business Logic Layer)

**Vị trí**: `app/Services/`

#### Vai Trò của Service
- Chứa **business logic** (nghiệp vụ)
- Xử lý các quy tắc nghiệp vụ phức tạp
- Kết hợp nhiều Repository nếu cần
- Xử lý transactions, events, notifications

#### Cấu Trúc Service
```php
<?php

namespace App\Services;

use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Exceptions\NotFoundException;

class ProductService
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {
    }

    /**
     * Get all products with filters
     */
    public function getAll(int $perPage = 12, ?string $search = null): LengthAwarePaginator
    {
        return $this->productRepository->getAll($perPage, $search);
    }

    /**
     * Create product with business logic
     */
    public function create(array $data): array
    {
        // Business logic: Validate, transform data, etc.
        if (isset($data['price']) && $data['price'] < 0) {
            throw new \InvalidArgumentException('Price cannot be negative');
        }
        
        // Call repository
        $product = $this->productRepository->create($data);
        
        // Additional logic: Send event, notification, etc.
        // event(new ProductCreated($product));
        
        return $product->toArray();
    }
}
```

#### Quy Tắc Viết Service

1. **Chỉ chứa business logic**, không chứa code tương tác database trực tiếp
2. **Inject Repository Interface**, không inject Model trực tiếp
3. **Xử lý Exception**: Throw custom exceptions khi cần
4. **Return type**: Luôn định nghĩa return type rõ ràng

#### Ví Dụ Service Phức Tạp
```php
public function createOrder(array $data): array
{
    DB::beginTransaction();
    try {
        // 1. Validate stock
        foreach ($data['items'] as $item) {
            $stock = $this->stockRepository->getStock($item['product_id']);
            if ($stock < $item['quantity']) {
                throw new BusinessLogicException('Insufficient stock');
            }
        }
        
        // 2. Create order
        $order = $this->orderRepository->create($data);
        
        // 3. Create order items
        foreach ($data['items'] as $item) {
            $this->orderItemRepository->create([
                'order_id' => $order->id,
                ...$item
            ]);
        }
        
        // 4. Update stock
        foreach ($data['items'] as $item) {
            $this->stockRepository->decrease($item['product_id'], $item['quantity']);
        }
        
        // 5. Calculate loyalty points
        $points = $this->loyaltyService->calculatePoints($order->total);
        $this->loyaltyService->addPoints($order->user_id, $points);
        
        DB::commit();
        return $order->toArray();
    } catch (\Exception $ex) {
        DB::rollBack();
        throw $ex;
    }
}
```

---

### 4. Repositories (Data Access Layer)

**Vị trí**: 
- Interface: `app/Repositories/Contracts/`
- Implementation: `app/Repositories/`

#### Vai Trò của Repository
- **Tách biệt** database logic khỏi business logic
- Cung cấp **interface** để dễ test và mock
- Tập trung các **database queries** ở một nơi

#### Tạo Repository Interface
```php
<?php
// app/Repositories/Contracts/ProductRepositoryInterface.php

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    public function getAll(int $perPage = 15, ?string $search = null): LengthAwarePaginator;
    
    public function findById(int $id): ?Product;
    
    public function create(array $data): Product;
    
    public function update(Product $product, array $data): Product;
    
    public function delete(Product $product): bool;
}
```

#### Implement Repository
```php
<?php
// app/Repositories/ProductRepository.php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductRepository implements ProductRepositoryInterface
{
    public function getAll(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        $query = Product::query();
        
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
        
        return $query->paginate($perPage);
    }
    
    public function findById(int $id): ?Product
    {
        return Product::find($id);
    }
    
    public function create(array $data): Product
    {
        return Product::create($data);
    }
    
    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        return $product->fresh();
    }
    
    public function delete(Product $product): bool
    {
        return $product->delete();
    }
}
```

#### Đăng Ký Repository trong Service Provider
```php
// app/Providers/AppServiceProvider.php

use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\ProductRepository;

public function register(): void
{
    $this->app->bind(
        ProductRepositoryInterface::class,
        ProductRepository::class
    );
}
```

#### Quy Tắc Viết Repository

1. **Chỉ chứa database queries**, không chứa business logic
2. **Return Model hoặc Collection**, không return array trừ khi cần thiết
3. **Sử dụng Eloquent** thay vì Query Builder khi có thể
4. **Xử lý relationships** trong Repository nếu cần

---

### 5. Models (Eloquent ORM)

**Vị trí**: `app/Models/`

#### Cấu Trúc Model Cơ Bản
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'price',
        'category_id',
    ];
    
    protected $casts = [
        'price' => 'integer',
        'specs' => 'array',
    ];
    
    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }
    
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
```

#### Quy Tắc Viết Model

1. **$fillable**: Định nghĩa các field có thể mass assign
2. **$hidden**: Ẩn các field nhạy cảm khi serialize
3. **$casts**: Chuyển đổi kiểu dữ liệu (array, json, datetime)
4. **Relationships**: Định nghĩa relationships với các model khác
5. **Accessors/Mutators**: Nếu cần transform data

---

### 6. Form Requests (Validation)

**Vị trí**: `app/Http/Requests/`

#### Tạo Form Request
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Hoặc kiểm tra permission
    }
    
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:products,slug',
            'price' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
        ];
    }
    
    public function messages(): array
    {
        return [
            'name.required' => 'Tên sản phẩm là bắt buộc',
            'price.min' => 'Giá phải lớn hơn 0',
        ];
    }
}
```

#### Sử Dụng trong Controller
```php
public function store(CreateProductRequest $request): JsonResponse
{
    // Request đã được validate tự động
    $validated = $request->validated();
    $product = $this->productService->create($validated);
    // ...
}
```

#### Quy Tắc Validation

1. **Tạo FormRequest riêng** cho mỗi action nếu validation phức tạp
2. **Sử dụng validation rules** của Laravel
3. **Custom messages** bằng tiếng Việt nếu cần
4. **authorize()**: Kiểm tra permission nếu cần

---

### 7. Middleware

**Vị trí**: `app/Http/Middleware/`

#### Middleware Có Sẵn

1. **Authenticate**: Kiểm tra user đã đăng nhập chưa
2. **AdminMiddleware**: Kiểm tra user có role admin không

#### Tạo Middleware Mới
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CustomMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Logic trước khi vào controller
        if (!auth()->check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated'
            ], 401);
        }
        
        $response = $next($request);
        
        // Logic sau khi controller xử lý xong
        
        return $response;
    }
}
```

#### Đăng Ký Middleware
```php
// bootstrap/app.php hoặc app/Http/Kernel.php
protected $middlewareGroups = [
    'api' => [
        // ...
        \App\Http\Middleware\CustomMiddleware::class,
    ],
];
```

---

### 8. Exceptions (Custom Exceptions)

**Vị trí**: `app/Exceptions/`

#### Exceptions Có Sẵn
- `AuthenticationException`: Lỗi xác thực
- `NotFoundException`: Không tìm thấy resource
- `ValidationException`: Lỗi validation
- `BusinessLogicException`: Lỗi nghiệp vụ

#### Tạo Exception Mới
```php
<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomException extends Exception
{
    protected $message = 'Custom error message';
    protected $code = 400;
    
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $this->getMessage(),
        ], $this->getCode());
    }
}
```

#### Sử Dụng Exception
```php
// Trong Service
if (!$product) {
    throw new NotFoundException("Product not found");
}
```

---

### 9. Helpers

**Vị trí**: `app/Helpers/`

#### Helper Functions Có Sẵn

1. **Helper::addLog()**: Ghi log hoạt động
2. **Helper::trackingError()**: Ghi log lỗi
3. **LanguageHelper::apiMessage()**: Lấy message đa ngôn ngữ

#### Sử Dụng Helper
```php
use App\Helpers\Helper;
use App\Helpers\LanguageHelper;

// Log activity
Helper::addLog([
    'action' => 'create_product',
    'obj_action' => json_encode([$productId]),
]);

// Get translated message
$message = LanguageHelper::apiMessage('product_created');
```

---

### 10. Traits

**Vị trí**: `app/Traits/`

#### Trait Có Sẵn: TranslatableResponse

Trait này cung cấp các method để trả về response với message đa ngôn ngữ.

```php
use App\Traits\TranslatableResponse;

class ProductController extends Controller
{
    use TranslatableResponse;
    
    public function index(): JsonResponse
    {
        $products = $this->productService->getAll();
        
        return $this->successResponse('products_loaded', $products);
    }
}
```

---

## Quy Trình Phát Triển Tính Năng Mới

### Ví Dụ: Thêm Tính Năng Quản Lý Coupon

#### Bước 1: Tạo Migration
```bash
php artisan make:migration create_coupons_table
```

```php
// database/migrations/xxxx_create_coupons_table.php
public function up(): void
{
    Schema::create('coupons', function (Blueprint $table) {
        $table->id();
        $table->string('code')->unique();
        $table->string('type'); // percentage, fixed
        $table->integer('value');
        $table->date('expires_at');
        $table->timestamps();
    });
}
```

#### Bước 2: Tạo Model
```bash
php artisan make:model Coupon
```

```php
// app/Models/Coupon.php
class Coupon extends Model
{
    protected $fillable = ['code', 'type', 'value', 'expires_at'];
    
    protected $casts = [
        'expires_at' => 'date',
    ];
}
```

#### Bước 3: Tạo Repository Interface
```bash
# Tạo file thủ công
# app/Repositories/Contracts/CouponRepositoryInterface.php
```

```php
interface CouponRepositoryInterface
{
    public function findByCode(string $code): ?Coupon;
    public function create(array $data): Coupon;
}
```

#### Bước 4: Tạo Repository Implementation
```bash
# Tạo file thủ công
# app/Repositories/CouponRepository.php
```

```php
class CouponRepository implements CouponRepositoryInterface
{
    public function findByCode(string $code): ?Coupon
    {
        return Coupon::where('code', $code)->first();
    }
    
    public function create(array $data): Coupon
    {
        return Coupon::create($data);
    }
}
```

#### Bước 5: Đăng Ký Repository
```php
// app/Providers/AppServiceProvider.php
$this->app->bind(
    CouponRepositoryInterface::class,
    CouponRepository::class
);
```

#### Bước 6: Tạo Service
```bash
# Tạo file thủ công
# app/Services/CouponService.php
```

```php
class CouponService
{
    public function __construct(
        private CouponRepositoryInterface $couponRepository
    ) {
    }
    
    public function validateCoupon(string $code): array
    {
        $coupon = $this->couponRepository->findByCode($code);
        
        if (!$coupon) {
            throw new NotFoundException('Coupon not found');
        }
        
        if ($coupon->expires_at < now()) {
            throw new BusinessLogicException('Coupon expired');
        }
        
        return $coupon->toArray();
    }
}
```

#### Bước 7: Tạo Form Request
```bash
php artisan make:request ValidateCouponRequest
```

#### Bước 8: Tạo Controller
```bash
php artisan make:controller CouponController
```

```php
class CouponController extends Controller
{
    public function __construct(
        private CouponService $couponService
    ) {
    }
    
    public function validate(ValidateCouponRequest $request): JsonResponse
    {
        try {
            $coupon = $this->couponService->validateCoupon($request->code);
            return response()->json([
                'status' => 'success',
                'data' => $coupon,
            ]);
        } catch (NotFoundException $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], 404);
        }
    }
}
```

#### Bước 9: Thêm Routes
```php
// routes/api.php
Route::post('coupons/validate', [CouponController::class, 'validate']);
```

#### Bước 10: Test
- Test API bằng Postman/Insomnia
- Viết Unit Test nếu cần

---

## Best Practices

### 1. Code Organization
- ✅ **Một Controller = Một Resource** (ProductController chỉ xử lý Product)
- ✅ **Service chứa business logic**, Repository chỉ chứa database queries
- ✅ **Đặt tên rõ ràng**: `getAllProducts()` thay vì `getAll()`

### 2. Error Handling
- ✅ **Luôn wrap trong try-catch** ở Controller
- ✅ **Throw custom exceptions** ở Service
- ✅ **Return HTTP status codes** phù hợp

### 3. Validation
- ✅ **Sử dụng FormRequest** cho validation phức tạp
- ✅ **Validate ở tầng Request**, không validate ở Service

### 4. Database
- ✅ **Sử dụng Eloquent** thay vì Query Builder khi có thể
- ✅ **Eager loading** để tránh N+1 queries: `Product::with('category')->get()`
- ✅ **Transactions** cho các operations phức tạp

### 5. Security
- ✅ **Validate và sanitize** tất cả input
- ✅ **Sử dụng middleware** để bảo vệ routes
- ✅ **Hash passwords** (Laravel tự động với `bcrypt`)

### 6. Performance
- ✅ **Pagination** cho danh sách lớn
- ✅ **Cache** cho dữ liệu ít thay đổi
- ✅ **Index database** cho các cột thường query

### 7. Code Style
- ✅ **PSR-12 coding standard**
- ✅ **Type hints** cho tất cả parameters và return types
- ✅ **Docblocks** cho các method phức tạp

---

## Troubleshooting

### Lỗi Thường Gặp

#### 1. "Class not found"
```bash
# Chạy lại autoload
composer dump-autoload
```

#### 2. "Route not found"
- Kiểm tra route đã được định nghĩa chưa
- Kiểm tra middleware có block request không
- Clear route cache: `php artisan route:clear`

#### 3. "Database connection error"
- Kiểm tra file `.env`
- Kiểm tra database đã tạo chưa
- Kiểm tra credentials

#### 4. "Repository binding error"
- Kiểm tra đã bind trong `AppServiceProvider` chưa
- Kiểm tra namespace đúng chưa

#### 5. "Session not working"
- Kiểm tra middleware `StartSession` đã được apply chưa
- Kiểm tra session driver trong `.env`

---

## Tài Liệu Tham Khảo

- [Laravel Documentation](https://laravel.com/docs)
- [Laravel Repository Pattern](https://laravel.com/docs/eloquent-repositories)
- [RESTful API Design](https://restfulapi.net/)

---

## Liên Hệ & Hỗ Trợ

Nếu có thắc mắc, vui lòng liên hệ team lead hoặc tạo issue trong repository.

**Chúc bạn code vui vẻ! 🚀**

