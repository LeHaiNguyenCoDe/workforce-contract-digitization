<?php

namespace Tests\Unit\Services;

use App\Exceptions\NotFoundException;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\ProductService;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProductService $productService;
    private $productRepositoryMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->productRepositoryMock = Mockery::mock(ProductRepositoryInterface::class);
        $this->productService = new ProductService($this->productRepositoryMock);
    }

    public function test_get_details_throws_exception_when_product_not_found(): void
    {
        $this->productRepositoryMock
            ->shouldReceive('findById')
            ->once()
            ->with(999)
            ->andReturn(null);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Product with ID 999 not found');

        $this->productService->getDetails(999);
    }

    public function test_get_all_returns_paginated_products(): void
    {
        $paginator = new LengthAwarePaginator([], 0, 12);

        $this->productRepositoryMock
            ->shouldReceive('getAll')
            ->once()
            ->with(12, null, null)
            ->andReturn($paginator);

        $result = $this->productService->getAll();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}

