<?php

namespace Tests\Unit\Services;

use App\Exceptions\NotFoundException;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\UserService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserService $userService;
    private $userRepositoryMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->userRepositoryMock = Mockery::mock(UserRepositoryInterface::class);
        $this->userService = new UserService($this->userRepositoryMock);
    }

    public function test_get_by_id_throws_exception_when_user_not_found(): void
    {
        $this->userRepositoryMock
            ->shouldReceive('findById')
            ->once()
            ->with(999)
            ->andReturn(null);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('User with ID 999 not found');

        $this->userService->getById(999);
    }

    public function test_get_all_returns_paginated_users(): void
    {
        $paginator = new LengthAwarePaginator([], 0, 15);

        $this->userRepositoryMock
            ->shouldReceive('getAll')
            ->once()
            ->with(15, null)
            ->andReturn($paginator);

        $result = $this->userService->getAll();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}

