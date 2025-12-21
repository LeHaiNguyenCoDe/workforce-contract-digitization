<?php

namespace Tests\Unit\Services;

use App\Exceptions\NotFoundException;
use App\Models\Cart;
use App\Repositories\Contracts\CartRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\CartService;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    private CartService $cartService;
    private $cartRepositoryMock;
    private $productRepositoryMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->cartRepositoryMock = Mockery::mock(CartRepositoryInterface::class);
        $this->productRepositoryMock = Mockery::mock(ProductRepositoryInterface::class);
        $this->cartService = new CartService($this->cartRepositoryMock, $this->productRepositoryMock);
    }

    public function test_add_item_throws_exception_when_product_not_found(): void
    {
        $this->productRepositoryMock
            ->shouldReceive('findById')
            ->once()
            ->with(999)
            ->andReturn(null);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Product with ID 999 not found');

        $request = Request::create('/cart/items', 'POST');
        $this->cartService->addItem($request, ['product_id' => 999, 'qty' => 1]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}

