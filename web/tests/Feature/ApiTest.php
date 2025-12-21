<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Language;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    protected string $baseUrl = '/api/v1';

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed languages for language-related tests
        $this->seed(LanguageSeeder::class);
    }

    /**
     * Test login endpoint
     */
    public function test_login_success(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'active' => true,
        ]);

        $response = $this->postJson("{$this->baseUrl}/login", [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                ],
            ])
            ->assertCookie('laravel_session');
    }

    /**
     * Test login with invalid credentials
     */
    public function test_login_invalid_credentials(): void
    {
        $response = $this->postJson("{$this->baseUrl}/login", [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
            ]);
    }

    /**
     * Test get current user (protected endpoint)
     */
    public function test_get_current_user(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'active' => true,
        ]);

        // Login first
        $loginResponse = $this->postJson("{$this->baseUrl}/login", [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // Get current user
        $response = $this->getJson("{$this->baseUrl}/me");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => ['id', 'name', 'email', 'created_at'],
            ])
            ->assertJson([
                'data' => [
                    'id' => $user->id,
                    'email' => $user->email,
                ],
            ]);
    }

    /**
     * Test get current user without authentication
     */
    public function test_get_current_user_unauthenticated(): void
    {
        $response = $this->getJson("{$this->baseUrl}/me");

        $response->assertStatus(401);
    }

    /**
     * Test get all products (public endpoint)
     */
    public function test_get_products(): void
    {
        $category = Category::first();
        if (!$category) {
            $category = Category::create([
                'name' => 'Test Category',
                'slug' => 'test-category',
            ]);
        }

        Product::create([
            'category_id' => $category->id,
            'name' => 'Test Product 1',
            'slug' => 'test-product-1',
            'price' => 100000,
        ]);

        $response = $this->getJson("{$this->baseUrl}/products");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data',
            ]);
    }

    /**
     * Test get product details
     */
    public function test_get_product_details(): void
    {
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

        $response = $this->getJson("{$this->baseUrl}/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data',
            ]);
    }

    /**
     * Test get product not found
     */
    public function test_get_product_not_found(): void
    {
        $response = $this->getJson("{$this->baseUrl}/products/99999");

        $response->assertStatus(404);
    }

    /**
     * Test get categories
     */
    public function test_get_categories(): void
    {
        $response = $this->getJson("{$this->baseUrl}/categories");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data',
            ]);
    }

    /**
     * Test get articles
     */
    public function test_get_articles(): void
    {
        $response = $this->getJson("{$this->baseUrl}/articles");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data',
            ]);
    }

    /**
     * Test get cart (session-based)
     */
    public function test_get_cart(): void
    {
        $response = $this->getJson("{$this->baseUrl}/cart");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data',
            ]);
    }

    /**
     * Test add item to cart
     */
    public function test_add_item_to_cart(): void
    {
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

        $response = $this->postJson("{$this->baseUrl}/cart/items", [
            'product_id' => $product->id,
            'qty' => 2,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
            ]);
    }

    /**
     * Test get orders (protected)
     */
    public function test_get_orders(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'active' => true,
        ]);

        // Login first
        $this->postJson("{$this->baseUrl}/login", [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response = $this->getJson("{$this->baseUrl}/orders");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data',
            ]);
    }

    /**
     * Test get orders without authentication
     */
    public function test_get_orders_unauthenticated(): void
    {
        $response = $this->getJson("{$this->baseUrl}/orders");

        $response->assertStatus(401);
    }

    /**
     * Test get wishlist (protected)
     */
    public function test_get_wishlist(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'active' => true,
        ]);

        // Login first
        $this->postJson("{$this->baseUrl}/login", [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response = $this->getJson("{$this->baseUrl}/wishlist");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data',
            ]);
    }

    /**
     * Test add to wishlist
     */
    public function test_add_to_wishlist(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
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
        $this->postJson("{$this->baseUrl}/login", [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson("{$this->baseUrl}/wishlist", [
            'product_id' => $product->id,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
            ]);
    }

    /**
     * Test get loyalty account
     */
    public function test_get_loyalty(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'active' => true,
        ]);

        // Login first
        $this->postJson("{$this->baseUrl}/login", [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response = $this->getJson("{$this->baseUrl}/loyalty");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data',
            ]);
    }

    /**
     * Test logout
     */
    public function test_logout(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'active' => true,
        ]);

        // Login first
        $this->postJson("{$this->baseUrl}/login", [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson("{$this->baseUrl}/logout");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        // Verify session is invalidated
        $meResponse = $this->getJson("{$this->baseUrl}/me");
        $meResponse->assertStatus(401);
    }

    /**
     * Test register new user
     */
    public function test_register_user(): void
    {
        $response = $this->postJson("{$this->baseUrl}/users", [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'data',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
        ]);
    }

    /**
     * Test get supported languages
     */
    public function test_get_supported_languages(): void
    {
        $response = $this->getJson("{$this->baseUrl}/language/supported");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data',
            ]);
    }

    /**
     * Test get current language
     */
    public function test_get_current_language(): void
    {
        $response = $this->getJson("{$this->baseUrl}/language", [
            'Accept-Language' => 'vi',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data',
            ]);
    }

    /**
     * Test set language preference (protected)
     */
    public function test_set_language(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'active' => true,
        ]);

        // Login first
        $this->postJson("{$this->baseUrl}/login", [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // Language set only accepts 'vi' or 'en'
        $response = $this->postJson("{$this->baseUrl}/language", [
            'locale' => 'vi',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);
    }

    /**
     * Test get promotions
     */
    public function test_get_promotions(): void
    {
        $response = $this->getJson("{$this->baseUrl}/promotions");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data',
            ]);
    }

    /**
     * Test create review (protected)
     */
    public function test_create_review(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
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
        $this->postJson("{$this->baseUrl}/login", [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson("{$this->baseUrl}/products/{$product->id}/reviews", [
            'rating' => 5,
            'content' => 'Great product!',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
            ]);
    }
}

