<?php

namespace Tests\Unit\Services;

use App\Exceptions\BusinessLogicException;
use App\Models\Stock;
use App\Models\Warehouse;
use App\Repositories\Contracts\InventoryLogRepositoryInterface;
use App\Repositories\Contracts\StockRepositoryInterface;
use App\Repositories\Contracts\WarehouseRepositoryInterface;
use App\Services\Admin\WarehouseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class WarehouseServiceTest extends TestCase
{
    use RefreshDatabase;

    private WarehouseService $warehouseService;
    private $warehouseRepositoryMock;
    private $stockRepositoryMock;
    private $inventoryLogRepositoryMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Create and authenticate a user
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $this->warehouseRepositoryMock = Mockery::mock(WarehouseRepositoryInterface::class);
        $this->stockRepositoryMock = Mockery::mock(StockRepositoryInterface::class);
        $this->inventoryLogRepositoryMock = Mockery::mock(InventoryLogRepositoryInterface::class);

        $this->warehouseService = new WarehouseService(
            $this->warehouseRepositoryMock,
            $this->stockRepositoryMock,
            $this->inventoryLogRepositoryMock
        );
    }

    public function test_adjust_stock_updates_quantity_and_logs_change(): void
    {
        // 1. Prepare
        $warehouse = Warehouse::factory()->create();
        $stock = Stock::factory()->create([
            'warehouse_id' => $warehouse->id,
            'quantity' => 100,
            'available_quantity' => 100
        ]);

        $data = [
            'product_id' => $stock->product_id,
            'quantity' => 150,
            'reason' => 'Inventory count',
        ];

        // 2. Mock
        $this->stockRepositoryMock
            ->shouldReceive('findByWarehouseProduct')
            ->once()
            ->andReturn($stock);

        $this->stockRepositoryMock
            ->shouldReceive('update')
            ->once()
            ->andReturn($stock);

        $this->inventoryLogRepositoryMock
            ->shouldReceive('create')
            ->once();

        // 3. Act
        $result = $this->warehouseService->adjustStock($warehouse->id, $data);

        // 4. Assert
        $this->assertIsArray($result);
    }

    public function test_outbound_stock_throws_exception_if_insufficient_available_quantity(): void
    {
        // 1. Prepare
        $warehouse = Warehouse::factory()->create();
        $stock = Stock::factory()->create([
            'warehouse_id' => $warehouse->id,
            'quantity' => 100,
            'available_quantity' => 10 // Only 10 available
        ]);

        $data = [
            'product_id' => $stock->product_id,
            'quantity' => 50, // Request 50
        ];

        // 2. Mock
        $this->stockRepositoryMock
            ->shouldReceive('findByWarehouseProduct')
            ->once()
            ->andReturn($stock);

        // 3. Assert & Act
        $this->expectException(BusinessLogicException::class);
        $this->expectExceptionMessage('Cannot outbound more than available quantity');

        $this->warehouseService->outboundStock($warehouse->id, $data);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
