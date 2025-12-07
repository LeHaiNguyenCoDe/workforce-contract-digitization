<?php

namespace Tests\Unit\Services;

use App\Exceptions\AuthenticationException;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\AuthService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthService $authService;
    private $userRepositoryMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->userRepositoryMock = Mockery::mock(UserRepositoryInterface::class);
        $this->authService = new AuthService($this->userRepositoryMock);
    }

    public function test_login_throws_exception_when_user_not_found(): void
    {
        $this->userRepositoryMock
            ->shouldReceive('findByEmail')
            ->once()
            ->with('test@example.com')
            ->andReturn(null);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid credentials');

        $this->authService->login(
            ['email' => 'test@example.com', 'password' => 'password'],
            false,
            Request::create('/login', 'POST')
        );
    }

    public function test_login_throws_exception_when_user_inactive(): void
    {
        $user = new User();
        $user->id = 1;
        $user->email = 'test@example.com';
        $user->active = false;

        $this->userRepositoryMock
            ->shouldReceive('findByEmail')
            ->once()
            ->with('test@example.com')
            ->andReturn($user);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Account is deactivated');

        $this->authService->login(
            ['email' => 'test@example.com', 'password' => 'password'],
            false,
            Request::create('/login', 'POST')
        );
    }

    public function test_login_throws_exception_when_password_invalid(): void
    {
        $user = new User();
        $user->id = 1;
        $user->email = 'test@example.com';
        $user->password = Hash::make('correct_password');
        $user->active = true;

        $this->userRepositoryMock
            ->shouldReceive('findByEmail')
            ->once()
            ->with('test@example.com')
            ->andReturn($user);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid credentials');

        $this->authService->login(
            ['email' => 'test@example.com', 'password' => 'wrong_password'],
            false,
            Request::create('/login', 'POST')
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}

