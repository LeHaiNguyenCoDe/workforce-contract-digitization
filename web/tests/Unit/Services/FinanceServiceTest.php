<?php

namespace Tests\Unit\Services;

use App\Exceptions\BusinessLogicException;
use App\Exceptions\NotFoundException;
use App\Models\FinanceTransaction;
use App\Models\Fund;
use App\Repositories\Contracts\FinanceRepositoryInterface;
use App\Services\Admin\FinanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class FinanceServiceTest extends TestCase
{
    use RefreshDatabase;

    private FinanceService $financeService;
    private $financeRepositoryMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->financeRepositoryMock = Mockery::mock(FinanceRepositoryInterface::class);
        $this->financeService = new FinanceService($this->financeRepositoryMock);
    }

    public function test_get_expense_by_id_throws_exception_when_not_found(): void
    {
        $this->financeRepositoryMock
            ->shouldReceive('findTransactionById')
            ->once()
            ->with(999)
            ->andReturn(null);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Transaction with ID 999 not found');

        $this->financeService->getExpenseById(999);
    }

    public function test_record_receipt_creates_transaction_and_updates_fund(): void
    {
        // 1. Prepare data
        $fund = Fund::factory()->create(['balance' => 1000]);
        $data = [
            'fund_id' => $fund->id,
            'amount' => 500,
            'description' => 'Test Receipt',
        ];

        // 2. Mock repository
        $this->financeRepositoryMock
            ->shouldReceive('findFundById')
            ->with($fund->id)
            ->andReturn($fund);

        $this->financeRepositoryMock
            ->shouldReceive('createTransaction')
            ->once()
            ->andReturn(new FinanceTransaction());

        // 3. Act
        $this->financeService->recordReceipt($data);

        // 4. Assert fund balance updated (1000 + 500)
        $this->assertEquals(1500, $fund->fresh()->balance);
    }

    public function test_record_payment_throws_exception_if_insufficient_balance(): void
    {
        // 1. Prepare data
        $fund = Fund::factory()->create(['balance' => 100]);
        $data = [
            'fund_id' => $fund->id,
            'amount' => 500,
            'description' => 'Test Payment',
        ];

        // 2. Mock repository
        $this->financeRepositoryMock
            ->shouldReceive('findFundById')
            ->with($fund->id)
            ->andReturn($fund);

        // 3. Assert & Act
        $this->expectException(BusinessLogicException::class);
        $this->expectExceptionMessage('Số dư quỹ không đủ để chi!');

        $this->financeService->recordPayment($data);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
