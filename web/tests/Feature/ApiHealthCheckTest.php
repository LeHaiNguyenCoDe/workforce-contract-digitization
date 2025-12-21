<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiHealthCheckTest extends TestCase
{
    use RefreshDatabase;

    protected string $baseUrl = '/api/v1';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LanguageSeeder::class);
    }

    /**
     * Test all Frontend Public API endpoints
     */
    public function test_frontend_public_endpoints(): void
    {
        // Language
        $this->getJson("{$this->baseUrl}/frontend/language")
            ->assertStatus(200);
        
        $this->getJson("{$this->baseUrl}/frontend/language/supported")
            ->assertStatus(200);

        // Categories
        $this->getJson("{$this->baseUrl}/frontend/categories")
            ->assertStatus(200);

        // Products
        $this->getJson("{$this->baseUrl}/frontend/products")
            ->assertStatus(200);

        // Articles
        $this->getJson("{$this->baseUrl}/frontend/articles")
            ->assertStatus(200);

        // Promotions
        $this->getJson("{$this->baseUrl}/frontend/promotions")
            ->assertStatus(200);

        // Cart
        $this->getJson("{$this->baseUrl}/frontend/cart")
            ->assertStatus(200);
    }

    /**
     * Test Frontend Authentication endpoints
     */
    public function test_frontend_auth_endpoints(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('Password123!'),
            'active' => true,
        ]);

        // Login
        $response = $this->postJson("{$this->baseUrl}/frontend/login", [
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ]);
        
        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'message', 'data']);

        // Get current user
        $this->getJson("{$this->baseUrl}/frontend/me")
            ->assertStatus(200);

        // Logout
        $this->postJson("{$this->baseUrl}/frontend/logout")
            ->assertStatus(200);
    }

    /**
     * Test Frontend Authenticated endpoints
     */
    public function test_frontend_authenticated_endpoints(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('Password123!'),
            'active' => true,
        ]);

        $category = Category::first();
        if (!$category) {
            $category = Category::create([
                'name' => 'Test Category',
                'slug' => 'test-category',
            ]);
        }

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'price' => 100000,
        ]);

        // Login first
        $this->postJson("{$this->baseUrl}/frontend/login", [
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ]);

        // Profile
        $this->getJson("{$this->baseUrl}/frontend/profile")
            ->assertStatus(200);

        // Orders
        $this->getJson("{$this->baseUrl}/frontend/orders")
            ->assertStatus(200);

        // Wishlist
        $this->getJson("{$this->baseUrl}/frontend/wishlist")
            ->assertStatus(200);

        $this->postJson("{$this->baseUrl}/frontend/wishlist", [
            'product_id' => $product->id,
        ])->assertStatus(201);

        // Loyalty
        $this->getJson("{$this->baseUrl}/frontend/loyalty")
            ->assertStatus(200);

        // Language preference
        $this->postJson("{$this->baseUrl}/frontend/language", [
            'locale' => 'vi',
        ])->assertStatus(200);

        // Reviews
        $this->postJson("{$this->baseUrl}/frontend/products/{$product->id}/reviews", [
            'rating' => 5,
            'content' => 'Great product!',
        ])->assertStatus(201);
    }

    /**
     * Test Admin endpoints (require admin role)
     */
    public function test_admin_endpoints(): void
    {
        // Create admin user
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('Password123!'),
            'active' => true,
        ]);

        // Attach admin role
        $adminRole = \App\Models\Role::firstOrCreate(
            ['name' => 'admin'],
            ['description' => 'Admin', 'is_active' => true]
        );
        $admin->roles()->sync([$adminRole->id]);

        // Login as admin
        $this->postJson("{$this->baseUrl}/frontend/login", [
            'email' => 'admin@example.com',
            'password' => 'Password123!',
        ]);

        // Test admin endpoints
        $this->getJson("{$this->baseUrl}/admin/users")
            ->assertStatus(200);

        $this->getJson("{$this->baseUrl}/admin/products")
            ->assertStatus(200);

        $this->getJson("{$this->baseUrl}/admin/categories")
            ->assertStatus(200);

        $this->getJson("{$this->baseUrl}/admin/orders")
            ->assertStatus(200);

        $this->getJson("{$this->baseUrl}/admin/promotions")
            ->assertStatus(200);

        $this->getJson("{$this->baseUrl}/admin/warehouses")
            ->assertStatus(200);

        $this->getJson("{$this->baseUrl}/admin/articles")
            ->assertStatus(200);

        $this->getJson("{$this->baseUrl}/admin/reviews")
            ->assertStatus(200);
    }

    /**
     * Test Admin endpoints without admin role (should fail)
     */
    public function test_admin_endpoints_without_role(): void
    {
        $user = User::factory()->create([
            'email' => 'customer@example.com',
            'password' => Hash::make('Password123!'),
            'active' => true,
        ]);

        // Login as regular user
        $this->postJson("{$this->baseUrl}/frontend/login", [
            'email' => 'customer@example.com',
            'password' => 'Password123!',
        ]);

        // Should get 403 Forbidden
        $this->getJson("{$this->baseUrl}/admin/users")
            ->assertStatus(403);
    }

    /**
     * Test error responses
     */
    public function test_error_responses(): void
    {
        // 404 Not Found
        $this->getJson("{$this->baseUrl}/frontend/products/99999")
            ->assertStatus(404);

        // 401 Unauthorized
        $this->getJson("{$this->baseUrl}/frontend/me")
            ->assertStatus(401);

        // 422 Validation Error
        $this->postJson("{$this->baseUrl}/frontend/register", [])
            ->assertStatus(422);
    }
}

