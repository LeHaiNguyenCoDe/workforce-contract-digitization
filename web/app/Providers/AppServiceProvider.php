<?php

namespace App\Providers;

use App\Repositories\Contracts\ArticleRepositoryInterface;
use App\Repositories\Contracts\BatchRepositoryInterface;
use App\Repositories\Contracts\CartRepositoryInterface;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\DebtRepositoryInterface;
use App\Repositories\Contracts\FinanceRepositoryInterface;
use App\Repositories\Contracts\LanguageRepositoryInterface;
use App\Repositories\Contracts\LoyaltyRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\PromotionRepositoryInterface;
use App\Repositories\Contracts\ReviewRepositoryInterface;
use App\Repositories\Contracts\SupplierRepositoryInterface;
use App\Repositories\Contracts\TranslationRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\WarehouseRepositoryInterface;
use App\Repositories\Contracts\WishlistRepositoryInterface;
use App\Repositories\Contracts\StockRepositoryInterface;
use App\Repositories\Contracts\InventoryLogRepositoryInterface;
use App\Repositories\Contracts\ProductImageRepositoryInterface;
use App\Repositories\Contracts\ProductVariantRepositoryInterface;
use App\Repositories\Contracts\PromotionItemRepositoryInterface;
use App\Repositories\Contracts\QuotationRepositoryInterface;
use App\Repositories\Contracts\ShipmentRepositoryInterface;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use App\Repositories\Contracts\TaskRepositoryInterface;
use App\Repositories\ArticleRepository;
use App\Repositories\BatchRepository;
use App\Repositories\CartRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\DebtRepository;
use App\Repositories\FinanceRepository;
use App\Repositories\LanguageRepository;
use App\Repositories\LoyaltyRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\PromotionRepository;
use App\Repositories\ReviewRepository;
use App\Repositories\SupplierRepository;
use App\Repositories\TranslationRepository;
use App\Repositories\UserRepository;
use App\Repositories\WarehouseRepository;
use App\Repositories\WishlistRepository;
use App\Repositories\StockRepository;
use App\Repositories\InventoryLogRepository;
use App\Repositories\ProductImageRepository;
use App\Repositories\ProductVariantRepository;
use App\Repositories\PromotionItemRepository;
use App\Repositories\QuotationRepository;
use App\Repositories\ShipmentRepository;
use App\Repositories\EmployeeRepository;
use App\Repositories\AttendanceRepository;
use App\Repositories\TaskRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind Repository interfaces to implementations
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(CartRepositoryInterface::class, CartRepository::class);
        $this->app->bind(PromotionRepositoryInterface::class, PromotionRepository::class);
        $this->app->bind(WishlistRepositoryInterface::class, WishlistRepository::class);
        $this->app->bind(LoyaltyRepositoryInterface::class, LoyaltyRepository::class);
        $this->app->bind(ReviewRepositoryInterface::class, ReviewRepository::class);
        $this->app->bind(ArticleRepositoryInterface::class, ArticleRepository::class);
        $this->app->bind(WarehouseRepositoryInterface::class, WarehouseRepository::class);
        $this->app->bind(LanguageRepositoryInterface::class, LanguageRepository::class);
        $this->app->bind(TranslationRepositoryInterface::class, TranslationRepository::class);
        $this->app->bind(StockRepositoryInterface::class, StockRepository::class);
        $this->app->bind(InventoryLogRepositoryInterface::class, InventoryLogRepository::class);
        $this->app->bind(ProductImageRepositoryInterface::class, ProductImageRepository::class);
        $this->app->bind(ProductVariantRepositoryInterface::class, ProductVariantRepository::class);
        $this->app->bind(PromotionItemRepositoryInterface::class, PromotionItemRepository::class);
        $this->app->bind(QuotationRepositoryInterface::class, QuotationRepository::class);
        $this->app->bind(ShipmentRepositoryInterface::class, ShipmentRepository::class);
        $this->app->bind(EmployeeRepositoryInterface::class, EmployeeRepository::class);
        $this->app->bind(AttendanceRepositoryInterface::class, AttendanceRepository::class);
        $this->app->bind(TaskRepositoryInterface::class, TaskRepository::class);




        // Finance & Debt repositories
        $this->app->bind(FinanceRepositoryInterface::class, FinanceRepository::class);
        $this->app->bind(DebtRepositoryInterface::class, DebtRepository::class);

        // Supplier & Batch repositories
        $this->app->bind(SupplierRepositoryInterface::class, SupplierRepository::class);
        $this->app->bind(BatchRepositoryInterface::class, BatchRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configure rate limiters for security
        \Illuminate\Support\Facades\RateLimiter::for('login', function ($request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(5)->by($request->ip());
        });

        \Illuminate\Support\Facades\RateLimiter::for('api', function ($request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Admin operations - more permissive for staff
        \Illuminate\Support\Facades\RateLimiter::for('admin', function ($request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });

        // Sensitive admin operations (delete, bulk operations)
        \Illuminate\Support\Facades\RateLimiter::for('admin-sensitive', function ($request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        // Cart operations
        \Illuminate\Support\Facades\RateLimiter::for('cart', function ($request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Order creation (prevent spam orders)
        \Illuminate\Support\Facades\RateLimiter::for('order-create', function ($request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        // Chat/messaging
        \Illuminate\Support\Facades\RateLimiter::for('chat', function ($request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(100)->by($request->user()?->id ?: $request->ip());
        });

        // Guest chat (more restrictive)
        \Illuminate\Support\Facades\RateLimiter::for('guest-chat', function ($request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(20)->by($request->ip());
        });

        // File uploads
        \Illuminate\Support\Facades\RateLimiter::for('upload', function ($request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(20)->by($request->user()?->id ?: $request->ip());
        });

        // Configure global pagination defaults
        $this->app->bind('pagination.default_per_page', function () {
            return 15;
        });

        $this->app->bind('pagination.max_per_page', function () {
            return 100;
        });
    }
}
