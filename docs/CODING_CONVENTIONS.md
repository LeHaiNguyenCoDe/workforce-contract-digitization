# 📝 Coding Conventions - Quy Ước Viết Code

Tài liệu này mô tả các quy ước viết code trong dự án để đảm bảo code nhất quán và dễ đọc.

## 📋 Mục Lục

1. [Naming Conventions](#naming-conventions)
2. [Code Structure](#code-structure)
3. [Comments & Documentation](#comments--documentation)
4. [Error Handling](#error-handling)
5. [Response Format](#response-format)
6. [Database Conventions](#database-conventions)

---

## Naming Conventions

### 1. Classes & Files

- **Controllers**: PascalCase, kết thúc bằng `Controller`
  ```php
  ProductController.php
  OrderController.php
  ```

- **Services**: PascalCase, kết thúc bằng `Service`
  ```php
  ProductService.php
  OrderService.php
  ```

- **Repositories**: PascalCase, kết thúc bằng `Repository`
  ```php
  ProductRepository.php
  UserRepository.php
  ```

- **Models**: PascalCase, số ít
  ```php
  Product.php
  Order.php
  User.php
  ```

- **Interfaces**: PascalCase, kết thúc bằng `Interface`
  ```php
  ProductRepositoryInterface.php
  ```

### 2. Methods

- **Controller methods**: camelCase, theo RESTful convention
  ```php
  public function index()    // GET /resources
  public function show()     // GET /resources/{id}
  public function store()    // POST /resources
  public function update()   // PUT /resources/{id}
  public function destroy()  // DELETE /resources/{id}
  ```

- **Service methods**: camelCase, mô tả rõ ràng
  ```php
  public function getAllProducts()
  public function createProduct()
  public function updateProductStock()
  ```

- **Repository methods**: camelCase, theo CRUD
  ```php
  public function getAll()
  public function findById()
  public function create()
  public function update()
  public function delete()
  ```

### 3. Variables

- **Variables**: camelCase
  ```php
  $productId
  $userName
  $totalAmount
  ```

- **Constants**: UPPER_SNAKE_CASE
  ```php
  const MAX_RETRY_COUNT = 3;
  const DEFAULT_PAGE_SIZE = 15;
  ```

### 4. Database

- **Tables**: snake_case, số nhiều
  ```php
  products
  order_items
  user_roles
  ```

- **Columns**: snake_case
  ```php
  created_at
  user_id
  is_active
  ```

---

## Code Structure

### 1. Controller Structure

```php
<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // 1. Constructor với Dependency Injection
    public function __construct(
        private ProductService $productService
    ) {
    }

    // 2. Methods theo thứ tự: index, show, store, update, destroy
    public function index(Request $request): JsonResponse
    {
        // Implementation
    }

    public function show(int $id): JsonResponse
    {
        // Implementation
    }
}
```

### 2. Service Structure

```php
<?php

namespace App\Services;

use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Exceptions\NotFoundException;

class ProductService
{
    // 1. Constructor
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {
    }

    // 2. Public methods (business logic)
    public function getAll(int $perPage = 12): LengthAwarePaginator
    {
        return $this->productRepository->getAll($perPage);
    }

    // 3. Private methods (helper methods)
    private function validateStock(int $productId, int $quantity): void
    {
        // Implementation
    }
}
```

### 3. Repository Structure

```php
<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;

class ProductRepository implements ProductRepositoryInterface
{
    // 1. Implement interface methods
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return Product::query()->paginate($perPage);
    }

    // 2. Helper methods nếu cần
    private function buildSearchQuery(string $search)
    {
        // Implementation
    }
}
```

---

## Comments & Documentation

### 1. Docblocks cho Public Methods

```php
/**
 * Get all products with filters
 *
 * @param  int  $perPage
 * @param  string|null  $search
 * @return LengthAwarePaginator
 */
public function getAll(int $perPage = 12, ?string $search = null): LengthAwarePaginator
{
    // Implementation
}
```

### 2. Inline Comments

- Chỉ comment khi logic phức tạp, không rõ ràng
- Comment bằng tiếng Anh hoặc tiếng Việt (nhất quán trong file)

```php
// ✅ Good: Comment giải thích logic phức tạp
// If this is main image, unset other main images
if ($data['is_main'] ?? false) {
    $this->productImageRepository->updateByProduct($productId, ['is_main' => false]);
}

// ❌ Bad: Comment không cần thiết
// Get product by ID
$product = Product::find($id);
```

### 3. TODO Comments

```php
// TODO: Implement caching for this query
// FIXME: Handle edge case when stock is 0
// NOTE: This method will be refactored in next sprint
```

---

## Error Handling

### 1. Controller Error Handling

```php
public function store(Request $request): JsonResponse
{
    try {
        // Main logic
        $product = $this->productService->create($request->all());
        
        return response()->json([
            'status' => 'success',
            'data' => $product,
        ], 201);
    } catch (ValidationException $ex) {
        // Handle validation errors
        return response()->json([
            'status' => 'error',
            'message' => 'Validation error',
            'errors' => $ex->errors(),
        ], 422);
    } catch (NotFoundException $ex) {
        // Handle not found errors
        return response()->json([
            'status' => 'error',
            'message' => $ex->getMessage(),
        ], 404);
    } catch (\Exception $ex) {
        // Handle unexpected errors
        Helper::trackingError('product', $ex->getMessage());
        return response()->json([
            'status' => 'error',
            'message' => 'An error occurred',
        ], 500);
    }
}
```

### 2. Service Error Handling

```php
public function create(array $data): array
{
    // Validate business rules
    if (isset($data['price']) && $data['price'] < 0) {
        throw new BusinessLogicException('Price cannot be negative');
    }
    
    // Check if product exists
    $existing = $this->productRepository->findBySlug($data['slug']);
    if ($existing) {
        throw new BusinessLogicException('Product with this slug already exists');
    }
    
    // Create product
    $product = $this->productRepository->create($data);
    return $product->toArray();
}
```

### 3. Custom Exceptions

```php
// Throw specific exceptions
throw new NotFoundException("Product with ID {$id} not found");
throw new BusinessLogicException("Insufficient stock");
throw new AuthenticationException("Invalid credentials");
```

---

## Response Format

### 1. Success Response

```php
// Single resource
return response()->json([
    'status' => 'success',
    'data' => $product,
], 200);

// Collection
return response()->json([
    'status' => 'success',
    'data' => $products,
], 200);

// With message
return response()->json([
    'status' => 'success',
    'message' => 'Product created successfully',
    'data' => $product,
], 201);
```

### 2. Error Response

```php
// Validation error
return response()->json([
    'status' => 'error',
    'message' => 'Validation error',
    'errors' => [
        'email' => ['The email field is required.'],
    ],
], 422);

// Not found
return response()->json([
    'status' => 'error',
    'message' => 'Product not found',
], 404);

// Generic error
return response()->json([
    'status' => 'error',
    'message' => 'An error occurred',
], 500);
```

### 3. Pagination Response

```php
// Laravel pagination tự động trả về format:
{
    "status": "success",
    "data": {
        "current_page": 1,
        "data": [...],
        "per_page": 15,
        "total": 100
    }
}
```

---

## Database Conventions

### 1. Migrations

```php
// Tên migration: snake_case, mô tả rõ ràng
create_products_table
add_featured_column_to_products_table

// Structure
public function up(): void
{
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
        $table->softDeletes(); // Nếu cần soft delete
    });
}
```

### 2. Model Relationships

```php
// BelongsTo
public function category(): BelongsTo
{
    return $this->belongsTo(Category::class);
}

// HasMany
public function images(): HasMany
{
    return $this->hasMany(ProductImage::class);
}

// BelongsToMany
public function tags(): BelongsToMany
{
    return $this->belongsToMany(Tag::class);
}
```

### 3. Query Best Practices

```php
// ✅ Good: Eager loading
Product::with('category', 'images')->get();

// ❌ Bad: N+1 query
$products = Product::all();
foreach ($products as $product) {
    $product->category; // Query trong loop
}

// ✅ Good: Select specific columns
Product::select('id', 'name', 'price')->get();

// ✅ Good: Use indexes
Product::where('category_id', $categoryId)->get();
```

---

## Type Hints

### 1. Luôn sử dụng Type Hints

```php
// ✅ Good
public function create(array $data): array
{
    // Implementation
}

// ❌ Bad
public function create($data)
{
    // Implementation
}
```

### 2. Return Types

```php
// Controller
public function index(): JsonResponse

// Service
public function getAll(): LengthAwarePaginator
public function findById(int $id): ?Product

// Repository
public function create(array $data): Product
public function delete(Product $product): bool
```

---

## Code Formatting

### 1. Indentation

- Sử dụng **4 spaces** (không dùng tabs)
- Laravel Pint sẽ tự động format

### 2. Line Length

- Giữ dòng code dưới 120 ký tự
- Xuống dòng khi cần

```php
// ✅ Good
$products = $this->productService->getAll(
    perPage: $perPage,
    search: $search,
    categoryId: $categoryId
);

// ❌ Bad
$products = $this->productService->getAll($perPage, $search, $categoryId);
```

### 3. Spacing

```php
// ✅ Good: Space around operators
$total = $price * $quantity;

// ✅ Good: Space after comma
$array = [1, 2, 3];

// ✅ Good: No space inside parentheses
if ($condition) {
    // code
}
```

---

## Testing Conventions

### 1. Test Naming

```php
// Feature tests
public function test_user_can_create_product()
public function test_user_cannot_create_product_without_authentication()

// Unit tests
public function test_product_service_calculates_total_correctly()
```

### 2. Test Structure

```php
public function test_user_can_create_product(): void
{
    // Arrange
    $user = User::factory()->create();
    $data = ['name' => 'Test Product', 'price' => 100];
    
    // Act
    $response = $this->actingAs($user)
        ->postJson('/api/v1/admin/products', $data);
    
    // Assert
    $response->assertStatus(201)
        ->assertJson(['status' => 'success']);
}
```

---

## Git Commit Messages

### Format

```
<type>: <subject>

<body>
```

### Types

- `feat`: Thêm tính năng mới
- `fix`: Sửa lỗi
- `docs`: Cập nhật tài liệu
- `style`: Format code (không ảnh hưởng logic)
- `refactor`: Refactor code
- `test`: Thêm/sửa tests
- `chore`: Cập nhật build, dependencies

### Examples

```
feat: Add coupon validation API

fix: Resolve N+1 query in product list

docs: Update API documentation

refactor: Extract order calculation logic to service
```

---

## Checklist Trước Khi Commit

- [ ] Code đã được format (chạy `php artisan pint`)
- [ ] Đã test API endpoint
- [ ] Không có lỗi linter
- [ ] Đã thêm comments cho logic phức tạp
- [ ] Response format đúng chuẩn
- [ ] Error handling đầy đủ
- [ ] Type hints đã được thêm

---

## Tools

### Laravel Pint (Code Formatter)

```bash
# Format code
php artisan pint

# Check formatting (không sửa)
php artisan pint --test
```

### PHPStan (Static Analysis)

```bash
# Chạy PHPStan (nếu có cấu hình)
vendor/bin/phpstan analyse
```

---

**Lưu ý**: Tuân thủ các quy ước này để code nhất quán và dễ maintain! 🎯

